<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyFreePeriod;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
class ScheduleDailyFreePeriodBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return ScheduleDailyFreePeriod::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = ScheduleDailyFreePeriod::KEY;
        $violation->entity = [
            "type" => ScheduleDailyFreePeriod::KEY,
            "day" => $blocker["day"],
            "max" => $blocker["max"],
            "min" => $blocker["min"]
        ];
        $violation->conflict = [
            "breach" => $blocker["breach"] ?? null,
            "min" => $blocker["min"] ?? null,
            "max" => $blocker["max"] ?? null,
            "total_free_period" => $blocker["total_free_period"] ?? null
        ];
        $violation->evidence = array_filter([
            "type" => $blocker["conflict"]["slot_type"] ?? null,
            "start_time" => $blocker["conflict"]["start_time"] ?? null,
            "end_time" => $blocker["conflict"]["end_time"] ?? null,
            "day" => $blocker["conflict"]["day"] ?? null,
        ]);
        return $violation;
    }
}
