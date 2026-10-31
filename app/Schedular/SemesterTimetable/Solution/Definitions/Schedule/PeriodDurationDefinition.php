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
use App\Schedular\SemesterTimetable\Solution\Support\PayloadPatch;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;

class PeriodDurationDefinition extends AbstractDefinition
{
    private const COMMON_DURATIONS = [120, 110, 100, 90, 80, 75, 60, 55, 50, 45, 40, 30];

    protected function name(): string
    {
        return 'Period duration';
    }

    public function priority(): int
    {
        return 20;
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

    public function describe(Subject $subject, ?SolutionContext $context = null): string
    {
        $minutes = $subject->get('duration_minutes') ?? $subject->get('period_duration');
        $day = $subject->day();

        return trim('Period duration' . ($minutes ? " of {$minutes} minutes" : '') . ($day ? ' on ' . ucfirst($day) : ''));
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        return $this->changes($issue->subject, $issue->day(), $context);
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $day = $obstacle->day() ?? ($issues[0] ?? null)?->day();

        return $day ? $this->changes($obstacle, $day, $context) : [];
    }

    private function changes(Subject $target, string $day, SolutionContext $context): array
    {
        [$start, $end] = $context->window($day);
        $span = $end - $start;
        $current = $context->periodDuration($day);
        $joints = $context->jointCourses($day);

        $candidates = collect([$span, ...self::COMMON_DURATIONS, ...$joints->map(fn($course) => $course['end'] - $course['start'])])
            ->unique()
            ->filter(fn($minutes) => $minutes > 0 && $minutes <= $span && $minutes !== $current)
            ->filter(fn($minutes) => $joints->every(
                fn($course) => ($course['end'] - $course['start']) % $minutes === 0 && ($course['start'] - $start) % $minutes === 0
            ));

        $even = $candidates->filter(fn($minutes) => $span % $minutes === 0);

        return ($even->isNotEmpty() ? $even : $candidates)
            ->sortBy(fn($minutes) => abs($current - $minutes))
            ->take(SolutionContext::MAX_ALTERNATIVES)
            ->map(fn($minutes) => $this->change($target, $day, $current, $minutes, $span))
            ->values()
            ->all();
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
            apply: PayloadPatch::dayException(Constraint::KEY, $day, ['duration_minutes' => $minutes]),
        );
    }
}
