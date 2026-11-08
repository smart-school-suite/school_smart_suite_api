<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions;

use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\Contracts\ConstraintDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

abstract class AbstractDefinition implements ConstraintDefinition
{
    abstract protected function name(): string;

    public function isLocked(): bool
    {
        return false;
    }

    public function describe(Subject $subject, ?SolutionContext $context = null): string
    {
        $identity = $context?->identify($subject);
        $date = $subject->date();
        $start = $subject->get('start_time');
        $end = $subject->get('end_time');

        return trim(implode(' ', array_filter([
            $this->name(),
            $identity ? "({$identity})" : null,
            $date ? 'on ' . Text::date($date) : null,
            $start && $end ? Text::time($start, $end) : null,
        ])));
    }

    public function shortName(Subject $subject, ?SolutionContext $context = null): string
    {
        $identity = $context?->identify($subject);
        return 'the ' . lcfirst($this->name()) . ($identity ? " ({$identity})" : '');
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return $this->describe($subject, $context) . ' could not be met';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'It clashes with ' . lcfirst($this->describe($obstacle, $context));
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        return [];
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        return [];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        throw new CannotApplyRemedy('This step has to be done by hand: ' . lcfirst(rtrim($step['label'] ?? 'review it', '.')) . '.');
    }

    protected function window(Subject $subject): ?array
    {
        $start = $subject->get('start_time');
        $end = $subject->get('end_time');
        if (!$start || !$end) {
            return null;
        }
        [$s, $e] = [Time::toMinutes($start), Time::toMinutes($end)];
        return $e > $s ? [$s, $e] : null;
    }

    /** Times taken by the issues an obstacle blocks, which a move must keep free. */
    protected function issueWindows(array $issues): array
    {
        return collect($issues)
            ->filter(fn(Issue $issue) => $issue->window() && $issue->subject->date())
            ->map(fn(Issue $issue) => ['date' => $issue->subject->date(), 'start' => $issue->window()[0], 'end' => $issue->window()[1]])
            ->values()
            ->all();
    }

    protected function remove(Subject $target, int $cost, ?string $label = null, ?SolutionContext $context = null): Remedy
    {
        return new Remedy(
            action: Remedy::REMOVE,
            target: $target,
            label: $label ?? 'Remove the request for ' . lcfirst($this->describe($target, $context)),
            changes: [],
            result: ['removed' => true],
            cost: $cost,
        );
    }

    protected function review(Subject $target, string $label): Remedy
    {
        return new Remedy(
            action: Remedy::REVIEW,
            target: $target,
            label: $label,
            changes: [],
            result: ['manual' => true],
            cost: Cost::MANUAL_REVIEW,
        );
    }

    /** Moves of a request to the nearest times $accepts. */
    protected function moves(Subject $target, SolutionContext $context, callable $accepts, int $baseCost, array $avoid = []): array
    {
        $window = $this->window($target);
        $date = $target->date();

        if (!$window || !$date) {
            return [];
        }

        return collect($context->nearestWindows($date, $window[0], $window[1], $accepts, $avoid))
            ->map(function (array $candidate) use ($target, $window, $date, $baseCost, $context) {
                $newStart = Time::toTime($candidate['start']);
                $newEnd = Time::toTime($candidate['end']);
                $where = ($candidate['date_changed'] ? Text::date($candidate['date']) . ' ' : '') . Text::time($newStart, $newEnd);

                return new Remedy(
                    action: Remedy::MOVE,
                    target: $target,
                    label: 'Move ' . lcfirst($this->describe($target, $context)) . " to {$where}",
                    changes: array_values(array_filter([
                        $candidate['date_changed'] ? ['field' => 'date', 'from' => $date, 'to' => $candidate['date']] : null,
                        ['field' => 'start_time', 'from' => Time::toTime($window[0]), 'to' => $newStart],
                        ['field' => 'end_time', 'from' => Time::toTime($window[1]), 'to' => $newEnd],
                    ])),
                    result: ['date' => $candidate['date'], 'start_time' => $newStart, 'end_time' => $newEnd],
                    cost: $baseCost + Cost::shift($candidate['shift']) + ($candidate['date_changed'] ? Cost::CHANGE_DATE : 0),
                    occupies: ['date' => $candidate['date'], 'start' => $candidate['start'], 'end' => $candidate['end']],
                );
            })
            ->all();
    }
}
