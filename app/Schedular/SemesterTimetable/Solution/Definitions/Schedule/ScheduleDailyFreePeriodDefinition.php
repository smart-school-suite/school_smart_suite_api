<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyFreePeriod as Constraint;
use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyFreePeriod as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\ScheduleDailyFreePeriodBlocker;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractLimitDefinition;

class ScheduleDailyFreePeriodDefinition extends AbstractLimitDefinition
{
    protected function name(): string
    {
        return 'Daily free period limit';
    }

    protected function unit(): string
    {
        return 'free periods per day';
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
        return ScheduleDailyFreePeriodBlocker::class;
    }
}
