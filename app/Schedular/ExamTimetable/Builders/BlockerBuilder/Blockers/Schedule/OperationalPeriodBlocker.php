<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class OperationalPeriodBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return OperationalPeriod::KEY;
    }

    protected function fields(): array
    {
        return ['entity_type', 'type', 'date', 'allowed_period', 'allowed_time', 'excluded_dates', 'longest_opening_minutes', 'duration'];
    }
}
