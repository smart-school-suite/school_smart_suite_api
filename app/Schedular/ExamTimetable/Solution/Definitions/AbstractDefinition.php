<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions;

use App\Schedular\ExamTimetable\Solution\Contracts\ConstraintDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use App\Schedular\ExamTimetable\Support\Time;

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
        $entity = $context?->identify($subject);

        return 'the ' . lcfirst($this->name()) . ($entity ? " ({$entity})" : '');
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return $this->describe($subject, $context) . ' could not be scheduled';
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
        $start = Time::toMinutes($subject->get('start_time'));
        $end = Time::toMinutes($subject->get('end_time'));

        return $start !== null && $end !== null && $end > $start ? [$start, $end] : null;
    }

    protected function issueWindows(array $issues): array
    {
        return collect($issues)
            ->filter(fn(Issue $issue) => $issue->window() !== null)
            ->map(fn(Issue $issue) => [...$issue->window(), 'date' => $issue->date()])
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

    protected function review(Subject $target, string $label, array $result = []): Remedy
    {
        return new Remedy(
            action: Remedy::REVIEW,
            target: $target,
            label: $label,
            changes: [],
            result: ['manual' => true, ...$result],
            cost: Cost::MANUAL_REVIEW,
        );
    }

    /**
     * Moves of $target to the nearest windows $accepts allows.
     * $length defaults to the current length of the target.
     */
    protected function moves(
        Subject $target,
        SolutionContext $context,
        callable $accepts,
        int $baseCost,
        array $avoid = [],
        ?int $length = null,
        array $occupiesWith = [],
    ): array {
        $window = $this->window($target);
        $date = $target->date();

        if (!$window || !$date) {
            return [];
        }

        $length ??= $window[1] - $window[0];

        return collect($context->nearestWindows($date, $window[0], $length, $accepts, $avoid, $target))
            ->map(fn(array $candidate) => $this->moveTo($target, $context, $candidate, $baseCost, $occupiesWith))
            ->all();
    }

    protected function moveTo(Subject $target, SolutionContext $context, array $candidate, int $baseCost, array $occupiesWith = []): Remedy
    {
        $window = $this->window($target);
        $date = $target->date();
        $newStart = Time::toTime($candidate['start']);
        $newEnd = Time::toTime($candidate['end']);
        $dateChanged = $candidate['date'] !== $date;
        $where = ($dateChanged ? Text::date($candidate['date']) . ' ' : '') . Text::time($newStart, $newEnd);

        return new Remedy(
            action: Remedy::MOVE,
            target: $target,
            label: 'Move ' . lcfirst($this->describe($target, $context)) . " to {$where}",
            changes: array_values(array_filter([
                $dateChanged ? ['field' => 'date', 'from' => $date, 'to' => $candidate['date']] : null,
                ['field' => 'start_time', 'from' => Time::toTime($window[0]), 'to' => $newStart],
                ['field' => 'end_time', 'from' => Time::toTime($window[1]), 'to' => $newEnd],
            ])),
            result: [
                'date' => $candidate['date'],
                'start_time' => $newStart,
                'end_time' => $newEnd,
            ],
            cost: $baseCost + Cost::shift($candidate['shift'] ?? 0) + ($dateChanged ? Cost::CHANGE_DATE : 0),
            occupies: [
                'date' => $candidate['date'],
                'start' => $candidate['start'],
                'end' => $candidate['end'],
                ...$occupiesWith,
            ],
        );
    }
}
