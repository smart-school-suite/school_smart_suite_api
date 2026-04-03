<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyFreePeriod;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
class ScheduleDailyPeriodBlocker implements BlockerBuilder
{
        public static function type(): string
    {
        return ScheduleDailyFreePeriod::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        return new BlockerDTO();
    }
}
