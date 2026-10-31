<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod as Constraint;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\BreakPeriodBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule\BreakPeriodDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Time;

class BreakPeriodDefinition extends AbstractDefinition
{
    protected function name(): string
    {
        return 'Break period';
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
        return BreakPeriodDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return BreakPeriodBlocker::class;
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        return $this->relocate($issue->subject, $issue->day(), [], $context);
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $day = $obstacle->day() ?? ($issues[0] ?? null)?->day();

        return $day ? $this->relocate($obstacle, $day, $this->issueWindows($issues, $day), $context) : [];
    }

    private function relocate(Subject $target, string $day, array $avoid, SolutionContext $context): array
    {
        $window = $this->window($target) ?? $context->breakWindow($day);

        if (!$window) {
            return [];
        }

        [$opStart, $opEnd] = $context->window($day);
        $length = $window[1] - $window[0];
        $duration = $context->periodDuration($day);
        $joints = $context->jointCourses($day);

        $positions = [];
        for ($start = $opStart + $duration; $start + $length <= $opEnd; $start += $duration) {
            $positions[] = $start;
        }
        foreach ($avoid as $blocked) {
            $positions[] = $blocked[0] - $length;
            $positions[] = $blocked[1];
        }

        $moves = collect($positions)
            ->unique()
            ->filter(fn($start) => $start > $opStart && $start + $length < $opEnd && $start !== $window[0])
            ->reject(fn($start) => $joints->contains(fn($course) => Time::overlaps($start, $start + $length, $course['start'], $course['end'])))
            ->reject(fn($start) => collect($avoid)->contains(fn($blocked) => Time::overlaps($start, $start + $length, $blocked[0], $blocked[1])))
            ->sortBy(fn($start) => abs($start - $window[0]))
            ->take(2)
            ->map(fn($start) => new Remedy(
                action: Remedy::MOVE,
                target: $target,
                label: 'Move the break on ' . ucfirst($day) . ' to ' . Time::toTime($start) . '-' . Time::toTime($start + $length),
                changes: [
                    ['field' => 'start_time', 'from' => Time::toTime($window[0]), 'to' => Time::toTime($start)],
                    ['field' => 'end_time', 'from' => Time::toTime($window[1]), 'to' => Time::toTime($start + $length)],
                ],
                result: [
                    'day' => $day,
                    'start_time' => Time::toTime($start),
                    'end_time' => Time::toTime($start + $length),
                    'scope' => 'day_exception',
                ],
                cost: Cost::MOVE_BREAK + Cost::shift($start - $window[0], 30),
                occupies: ['day' => $day, 'start' => $start, 'end' => $start + $length, 'exclusive' => true],
            ))
            ->values()
            ->all();

        $moves[] = new Remedy(
            action: Remedy::REMOVE,
            target: $target,
            label: 'Remove the break on ' . ucfirst($day),
            changes: [['field' => 'no_break_exceptions', 'from' => null, 'to' => $day]],
            result: ['day' => $day, 'no_break' => true],
            cost: Cost::REMOVE_BREAK,
        );

        return $moves;
    }
}
