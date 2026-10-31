<?php

namespace App\Schedular\SemesterTimetable\Solution\Planning;

use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Core\DiagnosticRegistry;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\Exceptions\HardConstraintFailureException;
use App\Schedular\SemesterTimetable\Grid\GridBuilder;
use App\Schedular\SemesterTimetable\Solution\Catalog\ConstraintCatalog;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\SolutionPath;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Time;
use Throwable;

class PathPlanner
{
    public const MAX_PATHS_PER_GROUP = 10;
    public const MAX_COMBINATIONS = 5;
    public const MAX_EXPLORED = 400;

    private SolutionContext $context;

    public function __construct(
        private ConstraintCatalog $catalog,
        private GridBuilder $gridBuilder,
        private DiagnosticRegistry $diagnostics,
    ) {}

    /**
     * @param Issue[] $issues
     * @return SolutionPath[]
     */
    public function plan(array $issues, SolutionContext $context): array
    {
        $this->context = $context;
        $paths = [];

        foreach ($this->group($issues) as $group) {
            array_push($paths, ...$this->planGroup($group));
        }

        return $paths;
    }

    private function planGroup(array $group): array
    {
        $paths = [];

        foreach ($group as $issue) {
            array_push($paths, ...$this->adjustRequest($issue), ...$this->clearObstacles($issue));
        }

        if (count($group) > 1) {
            foreach ($group as $kept) {
                array_push($paths, ...$this->keepOne($kept, $group));
            }

            array_push($paths, ...$this->sharedFix($group));
        }

        array_push($paths, ...$this->keepLocked($group));

        return $this->rank(array_map(fn(SolutionPath $path) => $path->withVerification($this->verify($path)), $paths));
    }

    private function adjustRequest(Issue $issue): array
    {
        return $this->build(SolutionPath::ADJUST_REQUEST, $issue, [$issue], [$this->adjustChoice($issue)]);
    }

    private function clearObstacles(Issue $issue): array
    {
        if (empty($issue->obstacles)) {
            return [];
        }

        $choices = array_map(fn(Subject $obstacle) => $this->obstacleChoice($obstacle, [$issue]), $issue->obstacles);

        return $this->build(SolutionPath::CLEAR_OBSTACLES, $issue, [$issue], $choices);
    }

    private function keepOne(Issue $kept, array $group): array
    {
        $groupIds = array_map(fn(Issue $issue) => $issue->id(), $group);
        $keptWindow = $kept->window();
        $choices = [];

        foreach ($group as $other) {
            if ($other->id() === $kept->id()) {
                continue;
            }

            if ($this->locked($other->type())) {
                return [];
            }

            $choices[] = $this->adjustChoice($other, fn(Remedy $remedy) => !($keptWindow && $remedy->occupies
                && $remedy->occupies['day'] === $kept->day()
                && Time::overlaps($remedy->occupies['start'], $remedy->occupies['end'], $keptWindow[0], $keptWindow[1])));
        }

        foreach ($kept->obstacles as $obstacle) {
            if (!in_array($obstacle->id, $groupIds, true)) {
                $choices[] = $this->obstacleChoice($obstacle, [$kept]);
            }
        }

        return $this->build(SolutionPath::KEEP_ONE, $kept, $group, $choices);
    }

    private function sharedFix(array $group): array
    {
        $obstacles = [];

        foreach ($group as $issue) {
            foreach ($issue->obstacles as $obstacle) {
                $obstacles[$obstacle->id]['subject'] = $obstacle;
                $obstacles[$obstacle->id]['issues'][] = $issue;
            }
        }

        if (!collect($obstacles)->contains(fn($entry) => count($entry['issues']) > 1)) {
            return [];
        }

        $choices = array_map(fn($entry) => $this->obstacleChoice($entry['subject'], $entry['issues']), array_values($obstacles));

        return $this->build(SolutionPath::SHARED_FIX, $group[0], $group, $choices);
    }

    private function keepLocked(array $group): array
    {
        $locked = [];

        foreach ($group as $issue) {
            foreach ($issue->obstacles as $obstacle) {
                if ($this->locked($obstacle->type)) {
                    $locked[$obstacle->id]['subject'] = $obstacle;
                    $locked[$obstacle->id]['issues'][$issue->id()] = $issue;
                }
            }
        }

        $paths = [];

        foreach ($locked as $entry) {
            $issues = array_values($entry['issues']);

            if (count($issues) < 2) {
                continue;
            }

            $choices = array_map(fn(Issue $issue) => $this->adjustChoice($issue), $issues);
            array_push($paths, ...$this->build(SolutionPath::KEEP_ONE, $issues[0], $issues, $choices, $entry['subject']));
        }

        return $paths;
    }

    private function adjustChoice(Issue $issue, ?callable $accept = null): array
    {
        $definition = $this->catalog->for($issue->type());

        return [
            'priority' => $definition->priority(),
            'options' => fn(SolutionContext $context) => $definition->isLocked() ? [] : $this->best(array_filter(
                $this->safely(fn() => $definition->adjustRequest($issue, $context)),
                $accept ?? fn() => true
            )),
        ];
    }

    private function obstacleChoice(Subject $obstacle, array $issues): array
    {
        $definition = $this->catalog->for($obstacle->type);

        return [
            'priority' => $definition->priority(),
            'options' => fn(SolutionContext $context) => $definition->isLocked() ? [] : $this->best(
                $this->safely(fn() => $definition->clearObstacle($obstacle, $issues, $context))
            ),
        ];
    }

    private function build(string $strategy, Issue $primary, array $issues, array $choices, ?Subject $kept = null): array
    {
        return array_map(
            fn(array $steps) => $this->path($strategy, $primary, $issues, $steps, $kept),
            $this->combine($choices)
        );
    }

    private function combine(array $choices): array
    {
        usort($choices, fn($a, $b) => $a['priority'] <=> $b['priority']);

        $results = [];
        $explored = 0;

        $walk = function (int $index, array $chosen, SolutionContext $context) use (&$walk, &$results, &$explored, $choices) {
            if ($explored++ > self::MAX_EXPLORED || count($results) >= self::MAX_COMBINATIONS * 4) {
                return;
            }

            if ($index === count($choices)) {
                $results[] = array_values($chosen);
                return;
            }

            foreach ($choices[$index]['options']($context) as $remedy) {
                if (isset($chosen[$remedy->id])) {
                    $walk($index + 1, $chosen, $context);
                    continue;
                }

                if (!$this->compatible($chosen, $remedy)) {
                    continue;
                }

                $next = $remedy->apply ? $context->withPayload(($remedy->apply)($context->payload)) : $context;
                $walk($index + 1, [...$chosen, $remedy->id => $remedy], $next);
            }
        };

        $walk(0, [], $this->context);

        return collect($results)
            ->sortBy(fn(array $steps) => array_sum(array_map(fn(Remedy $remedy) => $remedy->cost, $steps)))
            ->take(self::MAX_COMBINATIONS)
            ->values()
            ->all();
    }

    private function verify(SolutionPath $path): ?bool
    {
        if ($path->severity !== Issue::HARD) {
            return null;
        }

        $payload = $this->context->payload;
        foreach ($path->steps as $remedy) {
            if ($remedy->apply) {
                $payload = ($remedy->apply)($payload);
            }
        }

        $state = new State();

        try {
            $this->gridBuilder->buildGrid($state, $payload);
        } catch (HardConstraintFailureException) {
        } catch (Throwable $exception) {
            report($exception);
            return false;
        }

        $remaining = $this->diagnostics->build($state->violations['hard'] ?? [])
            ->map(fn($diagnostic) => $diagnostic->constraint_failed['id'] ?? null)
            ->filter()
            ->all();

        $fixed = array_map(fn(Issue $issue) => $issue->id(), $path->issues);

        return !array_intersect($fixed, $remaining);
    }

    private function compatible(array $chosen, Remedy $candidate): bool
    {
        foreach ($chosen as $remedy) {
            if ($remedy->target->id === $candidate->target->id || $remedy->overlapsWith($candidate)) {
                return false;
            }
        }

        return $this->enoughHalls($chosen, $candidate);
    }

    private function enoughHalls(array $chosen, Remedy $candidate): bool
    {
        $slot = $candidate->occupies;

        if (empty($slot['needs_hall'])) {
            return true;
        }

        $sharing = collect($chosen)->filter(fn(Remedy $remedy) => !empty($remedy->occupies['needs_hall'])
            && $remedy->occupies['day'] === $slot['day']
            && Time::overlaps($remedy->occupies['start'], $remedy->occupies['end'], $slot['start'], $slot['end']))->count();

        return $sharing === 0 || $this->context->freeHalls($slot['day'], $slot['start'], $slot['end'])->count() > $sharing;
    }

    private function locked(string $type): bool
    {
        return $this->catalog->for($type)->isLocked();
    }

    private function best(array $remedies): array
    {
        return collect($remedies)
            ->filter(fn($remedy) => $remedy instanceof Remedy)
            ->unique(fn(Remedy $remedy) => $remedy->id)
            ->sortBy(fn(Remedy $remedy) => $remedy->cost)
            ->take(SolutionContext::MAX_ALTERNATIVES)
            ->values()
            ->all();
    }

    private function rank(array $paths): array
    {
        $paths = collect($paths)
            ->unique(fn(SolutionPath $path) => $path->signature() . '|' . implode(',', array_map(fn(Issue $issue) => $issue->id(), $path->issues)))
            ->values();

        if ($paths->contains(fn(SolutionPath $path) => $path->verified === true)) {
            $paths = $paths->reject(fn(SolutionPath $path) => $path->verified === false)->values();
        }

        $ranked = $paths
            ->reject(fn(SolutionPath $path) => $paths->contains(fn(SolutionPath $other) => $other !== $path && $this->dominates($other, $path)))
            ->sortBy([
                fn(SolutionPath $a, SolutionPath $b) => $b->resolvedCount() <=> $a->resolvedCount(),
                fn(SolutionPath $a, SolutionPath $b) => $a->cost <=> $b->cost,
                fn(SolutionPath $a, SolutionPath $b) => count($a->steps) <=> count($b->steps),
            ])
            ->values();

        $bestPerDecision = $ranked->unique(fn(SolutionPath $path) => $path->strategy . '|' . ($path->kept?->id ?? $path->primary->id()));
        $rest = $ranked->reject(fn(SolutionPath $path) => $bestPerDecision->contains(fn(SolutionPath $best) => $best === $path));
        $kept = $bestPerDecision->concat($rest)->take(self::MAX_PATHS_PER_GROUP);

        return $ranked->filter(fn(SolutionPath $path) => $kept->contains(fn(SolutionPath $keptPath) => $keptPath === $path))->values()->all();
    }

    private function dominates(SolutionPath $better, SolutionPath $worse): bool
    {
        $betterSteps = array_map(fn(Remedy $remedy) => $remedy->id, $better->steps);
        $worseSteps = array_map(fn(Remedy $remedy) => $remedy->id, $worse->steps);
        $betterIssues = array_map(fn(Issue $issue) => $issue->id(), $better->issues);
        $worseIssues = array_map(fn(Issue $issue) => $issue->id(), $worse->issues);

        return count($betterSteps) <= count($worseSteps)
            && !array_diff($betterSteps, $worseSteps)
            && !array_diff($worseIssues, $betterIssues)
            && (count($betterSteps) < count($worseSteps) || count($betterIssues) > count($worseIssues));
    }

    private function path(string $strategy, Issue $primary, array $issues, array $steps, ?Subject $kept = null): SolutionPath
    {
        $severity = collect($issues)->contains(fn(Issue $issue) => $issue->severity === Issue::HARD) ? Issue::HARD : Issue::SOFT;

        return new SolutionPath($primary->day(), $severity, $strategy, $primary, array_values($issues), array_values($steps), $kept);
    }

    private function group(array $issues): array
    {
        $parent = [];
        $find = function (string $id) use (&$parent, &$find) {
            return $parent[$id] === $id ? $id : $parent[$id] = $find($parent[$id]);
        };

        foreach ($issues as $issue) {
            $parent[$issue->id()] = $issue->id();
        }

        foreach ($issues as $a) {
            foreach ($issues as $b) {
                if ($a->id() === $b->id() || $a->day() !== $b->day()) {
                    continue;
                }

                $linked = $a->isBlockedBy($b->id()) || $b->isBlockedBy($a->id())
                    || array_intersect($a->obstacleIds(), $b->obstacleIds());

                if ($linked) {
                    $parent[$find($a->id())] = $find($b->id());
                }
            }
        }

        $groups = [];
        foreach ($issues as $issue) {
            $groups[$find($issue->id())][$issue->id()] = $issue;
        }

        return array_map('array_values', array_values($groups));
    }

    private function safely(callable $callback): array
    {
        try {
            return $callback() ?? [];
        } catch (Throwable $exception) {
            report($exception);
            return [];
        }
    }
}
