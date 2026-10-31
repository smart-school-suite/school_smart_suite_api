<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration as Constraint;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\PeriodDurationBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule\PeriodDurationDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;

class PeriodDurationDefinition extends AbstractDefinition
{
    private const COMMON_DURATIONS = [120, 110, 100, 90, 80, 75, 60, 55, 50, 45, 40, 30];

    protected function name(): string
    {
        return 'Period duration';
    }

    public function constraintKey(): ?string
    {
        return Constraint::KEY;
    }

    public function violationKey(): ?string
    {
        return Violation::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return PeriodDurationDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return PeriodDurationBlocker::class;
    }

    public function describe(Subject $subject): string
    {
        $minutes = $subject->get('duration_minutes') ?? $subject->get('period_duration');
        $day = $subject->day();

        return trim('Period duration' . ($minutes ? " of {$minutes} minutes" : '') . ($day ? ' on ' . ucfirst($day) : ''));
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $day = $issue->day();
        [$start, $end] = $context->window($day);
        $span = $end - $start;
        $current = (int) ($issue->subject->get('duration_minutes') ?? $context->periodDuration($day));

        $candidates = collect([$span, ...self::COMMON_DURATIONS])
            ->unique()
            ->filter(fn($minutes) => $minutes > 0 && $minutes <= $span && $minutes !== $current);
        $even = $candidates->filter(fn($minutes) => $span % $minutes === 0);

        return ($even->isNotEmpty() ? $even : $candidates)
            ->sortBy(fn($minutes) => ($span % $minutes === 0 ? 0 : 10) + intdiv(abs($current - $minutes), 5))
            ->take(SolutionContext::MAX_ALTERNATIVES)
            ->map(fn($minutes) => $this->change($issue->subject, $day, $current, $minutes, $span))
            ->values()
            ->all();
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $day = $obstacle->day() ?? ($issues[0] ?? null)?->day();

        if (!$day) {
            return [];
        }

        [$start, $end] = $context->window($day);
        $lengths = collect($this->issueWindows($issues, $day))->map(fn($window) => $window[1] - $window[0])->unique();

        if ($lengths->count() !== 1 || $lengths->first() > $end - $start) {
            return [];
        }

        return [$this->change($obstacle, $day, $context->periodDuration($day), $lengths->first(), $end - $start)];
    }

    private function change(Subject $target, string $day, int $current, int $minutes, int $span): Remedy
    {
        $fits = intdiv($span, $minutes);

        return new Remedy(
            action: Remedy::MODIFY,
            target: $target,
            label: 'Change the period duration on ' . ucfirst($day) . " from {$current} to {$minutes} minutes ({$fits} period(s) fit the operational window)",
            changes: [['field' => 'duration_minutes', 'from' => $current, 'to' => $minutes]],
            result: ['day' => $day, 'duration_minutes' => $minutes, 'scope' => 'day_exception'],
            cost: Cost::CHANGE_PERIOD_DURATION + intdiv(abs($current - $minutes), 10) + ($span % $minutes === 0 ? 0 : 5),
        );
    }
}
