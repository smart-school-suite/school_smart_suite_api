<?php

namespace App\Schedular\ExamTimetable\Solution\Planning;

use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Core\DiagnosticRegistry;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Schedular\ExamTimetable\Solution\Catalog\ConstraintCatalog;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\SolutionPath;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\ExamPayload;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Support\Time;
use Throwable;

/**
 * Builds ranked solution paths for groups of connected issues (same date, blocking each other or sharing an
 * obstacle), with the same four strategies as the semester planner:
 * adjust_request, clear_obstacles, keep_one and shared_fix.
 *
 * Every path whose steps can all be applied automatically is verified by applying them to the payload and
 * running the scheduler again: verified is true when the issues it claims to fix are gone. Paths with a manual
 * step cannot be verified (verified: false). When a group has verified paths, unverified ones are dropped.
 */
class PathPlanner
{
    public const MAX_PATHS_PER_GROUP = 8;
    public const MAX_COMBINATIONS = 5;
    public const MAX_EXPLORED = 400;
    public const MAX_VERIFICATIONS = 60;

    private SolutionContext $context;
    private RequestEdit $edit;
    private int $verifications = 0;
    private array $hardIds = [];

    public function __construct(
        private ConstraintCatalog $catalog,
        private DiagnosticRegistry $diagnostics,
    ) {}

    /**
     * @param Issue[] $issues
     * @return SolutionPath[]
     */
    public function plan(array $issues, SolutionContext $context): array
    {
        $this->context = $context;
        $this->verifications = 0;
        $this->hardIds = collect($issues)->filter(fn(Issue $issue) => $issue->severity === Issue::HARD)->map(fn(Issue $issue) => $issue->id())->values()->all();
        $this->edit = new RequestEdit($this->catalog, collect($issues)
            ->flatMap(fn(Issue $issue) => [$issue->subject, ...$issue->obstacles])
            ->keyBy(fn(Subject $subject) => $subject->id)
            ->all());

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

        $paths = collect($paths)
            ->unique(fn(SolutionPath $path) => $path->signature() . '|' . implode(',', array_map(fn(Issue $issue) => $issue->id(), $path->issues)))
            ->sortBy(fn(SolutionPath $path) => $path->cost)
            ->values()
            ->all();

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
                && $remedy->occupies['date'] === $kept->date()
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

    /** A locked constraint (a joint course) blocking two or more requests: keep it, move all of them. */
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
            'options' => $definition->isLocked() ? [] : $this->best(array_filter(
                $this->safely(fn() => $definition->adjustRequest($issue, $this->context)),
                $accept ?? fn() => true
            )),
        ];
    }

    private function obstacleChoice(Subject $obstacle, array $issues): array
    {
        $definition = $this->catalog->for($obstacle->type);

        return [
            'priority' => $definition->priority(),
            'options' => $definition->isLocked() ? [] : $this->best(
                $this->safely(fn() => $definition->clearObstacle($obstacle, $issues, $this->context))
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

    /** Best-first combinations of one remedy per choice, never combining remedies that clash. */
    private function combine(array $choices): array
    {
        usort($choices, fn($a, $b) => $a['priority'] <=> $b['priority']);

        $results = [];
        $explored = 0;

        $walk = function (int $index, array $chosen) use (&$walk, &$results, &$explored, $choices) {
            if ($explored++ > self::MAX_EXPLORED || count($results) >= self::MAX_COMBINATIONS * 4) {
                return;
            }

            if ($index === count($choices)) {
                $results[] = array_values($chosen);
                return;
            }

            foreach ($choices[$index]['options'] as $remedy) {
                if (isset($chosen[$remedy->id])) {
                    $walk($index + 1, $chosen);
                    continue;
                }

                if (!$this->compatible($chosen, $remedy)) {
                    continue;
                }

                $walk($index + 1, [...$chosen, $remedy->id => $remedy]);
            }
        };

        $walk(0, []);

        return collect($results)
            ->sortBy(fn(array $steps) => array_sum(array_map(fn(Remedy $remedy) => $remedy->cost, $steps)))
            ->take(self::MAX_COMBINATIONS)
            ->values()
            ->all();
    }

    private function verify(SolutionPath $path): ?bool
    {
        if (collect($path->steps)->contains(fn(Remedy $remedy) => $remedy->isManual())) {
            return false;
        }

        if ($this->verifications >= self::MAX_VERIFICATIONS) {
            return null;
        }

        $this->verifications++;

        try {
            $request = ExamPayload::toRequest($this->context->payload);

            foreach ($path->steps() as $step) {
                $request = $this->catalog->for($step['target']['type'])->applyToRequest($request, $step, $this->edit);
            }

            $state = app(SchedularEngine::class)->schedule(ExamPayload::withRequest($this->context->payload, $request));
        } catch (CannotApplyRemedy) {
            return false;
        } catch (Throwable $exception) {
            report($exception);
            return false;
        }

        $remainingHard = $this->ids($state->violations['hard']);
        $remaining = [...$remainingHard, ...$this->ids($state->violations['soft'])];
        $fixed = array_map(fn(Issue $issue) => $issue->id(), $path->issues);

        // A fix must not create a blocking problem that was not there before.
        $introduced = array_diff($remainingHard, $this->hardIds);

        return !array_intersect($fixed, $remaining) && empty($introduced);
    }

    private function ids(array $violations): array
    {
        return $this->diagnostics->build($violations)
            ->map(fn($diagnostic) => $diagnostic->constraint_failed['id'] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    private function compatible(array $chosen, Remedy $candidate): bool
    {
        foreach ($chosen as $remedy) {
            if ($remedy->target->id === $candidate->target->id || $remedy->overlapsWith($candidate)) {
                return false;
            }
        }

        return true;
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
            ->take(SolutionContext::MAX_ALTERNATIVES + 1)
            ->values()
            ->all();
    }

    private function rank(array $paths): array
    {
        $paths = collect($paths);

        if ($paths->contains(fn(SolutionPath $path) => $path->verified === true)) {
            $paths = $paths->reject(fn(SolutionPath $path) => $path->verified === false)->values();
        }

        $ranked = $paths
            ->reject(fn(SolutionPath $path) => $paths->contains(fn(SolutionPath $other) => $other !== $path && $this->dominates($other, $path)))
            ->sortBy([
                fn(SolutionPath $a, SolutionPath $b) => $b->resolvedCount() <=> $a->resolvedCount(),
                fn(SolutionPath $a, SolutionPath $b) => ($b->verified === true) <=> ($a->verified === true),
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
        if ($worse->verified === true && $better->verified !== true) {
            return false;
        }

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

        return new SolutionPath($primary->date(), $severity, $strategy, $primary, array_values($issues), array_values($steps), $kept);
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
                if ($a->id() === $b->id() || $a->date() !== $b->date()) {
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
