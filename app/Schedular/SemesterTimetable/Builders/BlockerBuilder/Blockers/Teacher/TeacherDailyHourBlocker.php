<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherDailyHours;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class TeacherDailyHourBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return TeacherDailyHours::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = TeacherDailyHours::KEY;
        $violation->entity = [
            "type" => TeacherDailyHours::KEY,
            "teacher_id" => $blocker["teacher_id"],
            "day" => $blocker["day"],
            "max" => $blocker["max"],
            "min" => $blocker["min"]
        ];
        $violation->conflict = [
            "breach" => $blocker["breach"] ?? null,
            "min" => $blocker["min"] ?? null,
            "max" => $blocker["max"] ?? null,
            "total_hours" => $blocker["total_hours"] ?? null
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
