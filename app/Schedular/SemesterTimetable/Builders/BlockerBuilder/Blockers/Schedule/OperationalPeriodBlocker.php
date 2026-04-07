<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodConstraint;

class OperationalPeriodBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return OperationalPeriod::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = OperationalPeriod::KEY;
        $violation->entity = [
            "type" => OperationalPeriodConstraint::KEY,
            "start_time" => $blocker["start_time"] ?? null,
            "end_time" => $blocker["end_time"] ?? null,
            "day" => $blocker["day"] ?? null,
        ];
        $violation->evidence = [
            "start_time" => $blocker["start_time"] ?? null,
            "end_time" => $blocker["end_time"] ?? null,
            "day" => $blocker["day"] ?? null,
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
