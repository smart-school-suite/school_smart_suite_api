<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod as Constraint;
use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\OperationalPeriodBlocker;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\PayloadPatch;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Time;
use App\Schedular\SemesterTimetable\Solution\Support\Text;

class OperationalPeriodDefinition extends AbstractDefinition
{
    protected function name(): string
    {
        return 'Operational period';
    }

    public function priority(): int
    {
        return 10;
    }

    public function constraintKey(): ?string
    {
        return Constraint::KEY;
    }

    public function violationKey(): ?string
    {
        return Violation::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return OperationalPeriodBlocker::class;
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $day = $obstacle->day() ?? ($issues[0] ?? null)?->day();

        if (!$day) {
            return [];
        }

        [$start, $end] = $context->window($day);
        $needStart = $start;
        $needEnd = $end;
        $needMinutes = 0;

        foreach ($issues as $issue) {
            if ($issue->day() !== $day) {
                continue;
            }

            if ($issue->type() === PeriodDuration::KEY) {
                $needMinutes = max($needMinutes, (int) $issue->subject->get('duration_minutes', 0));
                continue;
            }

            if ($window = $issue->window()) {
                $needStart = min($needStart, $window[0]);
                $needEnd = max($needEnd, $window[1]);
            }
        }

        $candidates = [];

        if ($needStart < $start || $needEnd > $end) {
            $candidates[] = [$needStart, max($needEnd, $needStart + $needMinutes)];
        } elseif ($needMinutes > $end - $start) {
            $candidates[] = [$start, $start + $needMinutes];
            $candidates[] = [$end - $needMinutes, $end];
        }

        return collect($candidates)
            ->filter(fn($window) => $window[0] >= 0 && $window[1] <= Time::DAY_END)
            ->map(fn($window) => $this->extend($obstacle, $day, [$start, $end], $window))
            ->values()
            ->all();
    }

    private function extend(Subject $obstacle, string $day, array $current, array $new): Remedy
    {
        $from = Text::time(Time::toTime($current[0]), Time::toTime($current[1]));
        $to = Text::time(Time::toTime($new[0]), Time::toTime($new[1]));
        $extra = ($current[0] - $new[0]) + ($new[1] - $current[1]);

        return new Remedy(
            action: Remedy::MODIFY,
            target: $obstacle,
            label: 'Extend the school day on ' . ucfirst($day) . " to {$to} (it is {$from} now)",
            changes: [
                ['field' => 'start_time', 'from' => Time::toTime($current[0]), 'to' => Time::toTime($new[0])],
                ['field' => 'end_time', 'from' => Time::toTime($current[1]), 'to' => Time::toTime($new[1])],
            ],
            result: [
                'day' => $day,
                'start_time' => Time::toTime($new[0]),
                'end_time' => Time::toTime($new[1]),
                'scope' => 'day_exception',
            ],
            cost: Cost::EXTEND_OPERATIONAL_PERIOD + Cost::shift($extra),
            apply: PayloadPatch::dayException(Constraint::KEY, $day, [
                'start_time' => Time::toTime($new[0]),
                'end_time' => Time::toTime($new[1]),
            ]),
        );
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return 'The school day on ' . Text::day($subject->day()) . ' is too short';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'The school day on ' . Text::day($obstacle->day()) . ' only runs ' . Text::period($obstacle);
    }
}
