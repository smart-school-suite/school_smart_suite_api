<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions;

use App\Schedular\SemesterTimetable\Solution\Contracts\ConstraintDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Time;

abstract class AbstractDefinition implements ConstraintDefinition
{
    abstract protected function name(): string;

    public function isLocked(): bool
    {
        return false;
    }

    public function priority(): int
    {
        return 50;
    }

    public function constraintKey(): ?string
    {
        return null;
    }

    public function violationKey(): ?string
    {
        return null;
    }

    public function diagnosticBuilder(): ?string
    {
        return null;
    }

    public function blockerBuilder(): ?string
    {
        return null;
    }

    public function describe(Subject $subject, ?SolutionContext $context = null): string
    {
        $day = $subject->day();
        $start = $subject->get('start_time');
        $end = $subject->get('end_time');
        $identity = $context?->identify($subject);

        return trim(implode(' ', array_filter([
            $this->name(),
            $identity ? "({$identity})" : null,
            $day ? 'on ' . ucfirst($day) : null,
            $start && $end ? "{$start}-{$end}" : null,
        ])));
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        return [];
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        return [];
    }

    protected function window(Subject $subject): ?array
    {
        $start = Time::toMinutes($subject->get('start_time'));
        $end = Time::toMinutes($subject->get('end_time'));

        return $start !== null && $end !== null && $end > $start ? [$start, $end] : null;
    }

    protected function issueWindows(array $issues, ?string $day = null): array
    {
        return collect($issues)
            ->filter(fn(Issue $issue) => $issue->window() !== null && ($day === null || $issue->day() === $day))
            ->map(fn(Issue $issue) => [...$issue->window(), 'day' => $issue->day()])
            ->values()
            ->all();
    }

    protected function remove(Subject $target, int $cost, ?string $label = null, ?SolutionContext $context = null): Remedy
    {
        return new Remedy(
            action: Remedy::REMOVE,
            target: $target,
            label: $label ?? 'Remove ' . lcfirst($this->describe($target, $context)),
            changes: [],
            result: ['removed' => true],
            cost: $cost,
        );
    }

    protected function moves(
        Subject $target,
        SolutionContext $context,
        callable $accepts,
        int $baseCost,
        array $avoid = [],
        array $ignore = [],
        array $occupiesWith = [],
    ): array {
        $window = $this->window($target);
        $day = $target->day();

        if (!$window || !$day) {
            return [];
        }

        return collect($context->nearestWindows($day, $window[0], $window[1], $accepts, $avoid, $ignore))
            ->map(function ($candidate) use ($target, $window, $day, $baseCost, $occupiesWith, $context) {
                $newStart = Time::toTime($candidate['start']);
                $newEnd = Time::toTime($candidate['end']);
                $where = $candidate['day_changed'] ? ucfirst($candidate['day']) . " {$newStart}-{$newEnd}" : "{$newStart}-{$newEnd}";

                return new Remedy(
                    action: Remedy::MOVE,
                    target: $target,
                    label: 'Move ' . lcfirst($this->describe($target, $context)) . " to {$where}",
                    changes: array_values(array_filter([
                        $candidate['day_changed'] ? ['field' => 'day', 'from' => $day, 'to' => $candidate['day']] : null,
                        ['field' => 'start_time', 'from' => Time::toTime($window[0]), 'to' => $newStart],
                        ['field' => 'end_time', 'from' => Time::toTime($window[1]), 'to' => $newEnd],
                    ])),
                    result: [
                        'day' => $candidate['day'],
                        'start_time' => $newStart,
                        'end_time' => $newEnd,
                    ],
                    cost: $baseCost + Cost::shift($candidate['shift']) + ($candidate['day_changed'] ? Cost::CHANGE_DAY : 0),
                    occupies: [
                        'day' => $candidate['day'],
                        'start' => $candidate['start'],
                        'end' => $candidate['end'],
                        ...$occupiesWith,
                    ],
                );
            })
            ->all();
    }
}
