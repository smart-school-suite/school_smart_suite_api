<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyPeriod as Constraint;
use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyPeriod as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\ScheduleDailyPeriodBlocker;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractLimitDefinition;

class ScheduleDailyPeriodDefinition extends AbstractLimitDefinition
{
    protected function name(): string
    {
        return 'Daily period limit';
    }

    protected function unit(): string
    {
        return 'periods per day';
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
        return ScheduleDailyPeriodBlocker::class;
    }
}
