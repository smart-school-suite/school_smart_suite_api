<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyPeriod;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class ScheduleDailyPeriodBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return ScheduleDailyPeriod::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = ScheduleDailyPeriod::KEY;
        $violation->entity = [
            "type" => ScheduleDailyPeriod::KEY,
            "day" => $blocker["day"],
            "max" => $blocker["max"],
            "min" => $blocker["min"]
        ];
        $violation->conflict = [
            "breach" => $blocker["breach"] ?? null,
            "min" => $blocker["min"] ?? null,
            "max" => $blocker["max"] ?? null,
            "total_period" => $blocker["total_period"] ?? null
        ];
        $violation->evidence = array_filter([
            "type" => $blocker["conflict"]["slot_type"] ?? null,
            "start_time" => $blocker["conflict"]["start_time"] ?? null,
            "end_time" => $blocker["conflict"]["end_time"] ?? null,
            "day" => $blocker["conflict"]["day"] ?? null,
            "course_id" => $blocker["conflict"]["course_id"] ?? null,
            "teacher_id" => $blocker["conflict"]["teacher_id"] ?? null,
            "hall_id" => $blocker["conflict"]["hall_id"] ?? null
        ]);
        return $violation;
    }
}
