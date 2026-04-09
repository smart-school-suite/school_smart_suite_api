<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodBlockerConstant;
use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodConstraintConstant;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class BreakPeriodBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return BreakPeriodBlockerConstant::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = BreakPeriodBlockerConstant::KEY;
        $violation->entity = [
            "type" => BreakPeriodConstraintConstant::KEY,
            "start_time" => $blocker["start_time"],
            "end_time" => $blocker["end_time"],
            "day" => $blocker["day"],
        ];
        $violation->evidence = [
            "start_time" => $blocker["start_time"],
            "end_time" => $blocker["end_time"],
            "day" => $blocker["day"],
        ];
        $violation->conflict = array_filter([
            "type" => $blocker["conflict"]["slot_type"] ?? null,
            "start_time" => $blocker["conflict"]["start_time"] ?? null,
            "end_time" => $blocker["conflict"]["end_time"] ?? null,
            "day" => $blocker["conflict"]["day"] ?? null,
            "course_id" => $blocker["conflict"]["course_id"] ?? null,
            "teacher_id" => $blocker["conflict"]["teacher_id"] ?? null,
            "hall_id" => $blocker["conflict"]["hall_id"] ?? null,
        ]);
        return $violation;
    }
}
