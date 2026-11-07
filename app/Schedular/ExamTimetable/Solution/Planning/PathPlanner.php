<?php

namespace App\Schedular\ExamTimetable\Solution\Planning;

use App\Schedular\ExamTimetable\Context\ExamTimetableContext;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Schedular\ExamTimetable\Solution\Catalog\ConstraintCatalog;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\SolutionPath;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\RequestShape;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use Throwable;

/**
 * Builds solution paths for groups of connected issues, with the same four
 * strategies as the semester timetable (adjust the request, clear what is in
 * its way, keep one of competing requests, fix a shared cause).
 *
 * Every path without manual steps is verified by applying it to the
 * scheduler input and generating the timetable again: it is verified when
 * its issues are gone and no new problem appeared. Paths that fail this are
 * never offered. Paths with manual steps cannot be checked (verified: null).
 */
class PathPlanner
{
    public const MAX_PATHS_PER_GROUP = 8;
    public const MAX_COMBINATIONS = 4;
    public const MAX_EXPLORED = 300;

    private SolutionContext $context;
    private RequestEdit $edit;
    private array $originalIds = [];

    public function __construct(private ConstraintCatalog $catalog) {}

    /**
     * @param Issue[] $issues
     * @param Issue[] $allIssues every issue of the run, hard and soft, to spot new ones after a fix
     * @return SolutionPath[]
     */
    public function plan(array $issues, array $allIssues, SolutionContext $context): array
    {
        $this->context = $context;
        $this->originalIds = array_map(fn(Issue $issue) => $issue->id(), $allIssues);
        $this->edit = $this->editFor($allIssues);

        $paths = [];
        foreach ($this->group($issues) as $group) {
            array_push($paths, ...$this->planGroup($group));
        }

        return $paths;
    }

    public function editFor(array $issues): RequestEdit
    {
        $subjects = [];
        foreach ($issues as $issue) {
            $subjects[$issue->id()] = $issue->subject;
            foreach ($issue->obstacles as $obstacle) {
                $subjects[$obstacle->id] ??= $obstacle;
            }
        }
        return new RequestEdit($this->catalog, $subjects);
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

        $paths = collect($paths)
            ->unique(fn(SolutionPath $path) => $path->signature() . '|' . implode(',', array_map(fn(Issue $issue) => $issue->id(), $path->issues)))
            // The same change reached through a different target reads the same: keep one.
            ->unique(fn(SolutionPath $path) => $path->toArray()['summary'] . '|' . implode(',', array_map(fn(Issue $issue) => $issue->id(), $path->issues)))
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
        $choices = [];
        $groupIds = array_map(fn(Issue $issue) => $issue->id(), $group);
        $keptWindow = $kept->window();

        foreach ($group as $other) {
            if ($other->id() === $kept->id()) {
                continue;
            }
            if ($this->catalog->for($other->type())->isLocked()) {
                return [];
            }
            $choices[] = $this->adjustChoice($other, fn(Remedy $remedy) => !($keptWindow && $remedy->occupies
                && $remedy->occupies['date'] === $kept->date()
                && $remedy->occupies['start'] < $keptWindow[1] && $keptWindow[0] < $remedy->occupies['end']));
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

        $shared = array_filter($obstacles, fn(array $entry) => count($entry['issues']) > 1);
        if (!$shared) {
            return [];
        }

        $choices = array_map(fn(array $entry) => $this->obstacleChoice($entry['subject'], $entry['issues']), array_values($shared));

        return $this->build(SolutionPath::SHARED_FIX, $group[0], $group, $choices);
    }

    private function adjustChoice(Issue $issue, ?callable $accept = null): callable
    {
        $definition = $this->catalog->for($issue->type());

        return fn() => $definition->isLocked() ? [] : $this->best(array_filter(
            $this->safely(fn() => $definition->adjustRequest($issue, $this->context)),
            $accept ?? fn() => true
        ));
    }

    private function obstacleChoice(Subject $obstacle, array $issues): callable
    {
        $definition = $this->catalog->for($obstacle->type);

        return fn() => $definition->isLocked() ? [] : $this->best(
            $this->safely(fn() => $definition->clearObstacle($obstacle, $issues, $this->context))
        );
    }

    private function build(string $strategy, Issue $primary, array $issues, array $choices): array
    {
        return array_map(
            fn(array $steps) => $this->path($strategy, $primary, $issues, $steps, $strategy === SolutionPath::KEEP_ONE ? $primary->subject : null),
            $this->combine($choices)
        );
    }

    /** Best-first combinations of one remedy per choice; no path when a choice has no option. */
    private function combine(array $choices): array
    {
        $options = array_map(fn(callable $choice) => $choice(), $choices);

        if (empty($options) || in_array([], $options, true)) {
            return [];
        }

        $results = [];
        $explored = 0;

        $walk = function (int $index, array $chosen) use (&$walk, &$results, &$explored, $options) {
            if ($explored++ > self::MAX_EXPLORED || count($results) >= self::MAX_COMBINATIONS * 4) {
                return;
            }
            if ($index === count($options)) {
                $results[] = array_values($chosen);
                return;
            }
            foreach ($options[$index] as $remedy) {
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

    private function compatible(array $chosen, Remedy $candidate): bool
    {
        foreach ($chosen as $remedy) {
            if ($remedy->target->id === $candidate->target->id || $remedy->overlapsWith($candidate)) {
                return false;
            }
        }
        return true;
    }

    /** Applies the path to the scheduler input and generates again. */
    private function verify(SolutionPath $path): ?bool
    {
        if ($path->hasManualSteps()) {
            return null;
        }

        $payload = $this->context->payload;
        $request = RequestShape::fromInput($payload);

        try {
            foreach ($path->stepArrays() as $step) {
                $request = $this->catalog->for($step['target']['type'])->applyToRequest($request, $step, $this->edit);
            }
        } catch (CannotApplyRemedy) {
            return null;
        } catch (Throwable $exception) {
            report($exception);
            return false;
        }

        try {
            $response = app(SchedularEngine::class)->run(RequestShape::intoInput($payload, $request), withSolutions: false);
        } catch (Throwable $exception) {
            report($exception);
            return false;
        } finally {
            ExamTimetableContext::setRequestPayload($payload);
        }

        $remaining = [];
        $new = [];
        foreach (['hard', 'soft'] as $severity) {
            foreach ($response->diagnostics[$severity] as $diagnostic) {
                $id = $diagnostic->constraint_failed['id'];
                $remaining[] = $id;
                if (!in_array($id, $this->originalIds, true) && ($severity === 'hard' || $path->severity === Issue::SOFT)) {
                    $new[] = $id;
                }
            }
        }

        $fixed = array_map(fn(Issue $issue) => $issue->id(), $path->issues);

        return !array_intersect($fixed, $remaining) && empty($new);
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

    /** Paths the re-run proved wrong are dropped; verified paths come first. */
    private function rank(array $paths): array
    {
        $paths = collect($paths)->reject(fn(SolutionPath $path) => $path->verified === false);

        return $paths
            ->reject(fn(SolutionPath $path) => $paths->contains(fn(SolutionPath $other) => $other !== $path && $this->dominates($other, $path)))
            ->sortBy([
                fn(SolutionPath $a, SolutionPath $b) => $this->verifiedRank($a) <=> $this->verifiedRank($b),
                fn(SolutionPath $a, SolutionPath $b) => $b->resolvedCount() <=> $a->resolvedCount(),
                fn(SolutionPath $a, SolutionPath $b) => $a->cost <=> $b->cost,
                fn(SolutionPath $a, SolutionPath $b) => count($a->steps) <=> count($b->steps),
            ])
            ->take(self::MAX_PATHS_PER_GROUP)
            ->values()
            ->all();
    }

    private function verifiedRank(SolutionPath $path): int
    {
        return match ($path->verified) {
            true => 0,
            null => 1,
            false => 2,
        };
    }

    private function dominates(SolutionPath $better, SolutionPath $worse): bool
    {
        $betterSteps = array_map(fn(Remedy $remedy) => $remedy->id, $better->steps);
        $worseSteps = array_map(fn(Remedy $remedy) => $remedy->id, $worse->steps);
        $betterIssues = array_map(fn(Issue $issue) => $issue->id(), $better->issues);
        $worseIssues = array_map(fn(Issue $issue) => $issue->id(), $worse->issues);

        return $this->verifiedRank($better) <= $this->verifiedRank($worse)
            && count($betterSteps) <= count($worseSteps)
            && !array_diff($betterSteps, $worseSteps)
            && !array_diff($worseIssues, $betterIssues)
            && (count($betterSteps) < count($worseSteps) || count($betterIssues) > count($worseIssues));
    }

    private function path(string $strategy, Issue $primary, array $issues, array $steps, ?Subject $kept): SolutionPath
    {
        $severity = collect($issues)->contains(fn(Issue $issue) => $issue->severity === Issue::HARD) ? Issue::HARD : Issue::SOFT;
        return new SolutionPath($primary->date(), $severity, $strategy, $primary, array_values($issues), array_values($steps), $kept);
    }

    /** Issues on the same date that block each other or share an obstacle are fixed together. */
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
                if ($a->isBlockedBy($b->id()) || $b->isBlockedBy($a->id()) || array_intersect($a->obstacleIds(), $b->obstacleIds())) {
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
