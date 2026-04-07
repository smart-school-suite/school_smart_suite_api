<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course;

use App\Constant\Violation\SemesterTimetable\Course\CourseDailyFrequency;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
class CourseDailyFrequencyBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return CourseDailyFrequency::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = CourseDailyFrequency::KEY;
        $violation->entity = [
            "type" => CourseDailyFrequency::KEY,
            "day" => $blocker["day"],
            "max" => $blocker["max"],
            "min" => $blocker["min"]
        ];
        $violation->conflict = [
            "breach" => $blocker["breach"] ?? null,
            "min" => $blocker["min"] ?? null,
            "max" => $blocker["max"] ?? null,
            "course_frequency" => $blocker["course_frequency"] ?? null
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
