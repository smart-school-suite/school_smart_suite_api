<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course;

use App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot as CourseRequestedSlotViolationConstant;
use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot as CourseRequestedTimeSlotConstraintConstant;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
class CourseRequestedTimeSlotBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return CourseRequestedSlotViolationConstant::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = self::type();
        $violation->entity = [
            "type" => CourseRequestedTimeSlotConstraintConstant::KEY,
            "course_id" => $blocker['course_id'],
            "day" => $blocker['day'],
            "start_time" => $blocker['start_time'],
            "end_time" => $blocker['end_time'],
        ];
        $violation->evidence = [
            "day" => $blocker['day'],
            "start_time" => $blocker['start_time'],
            "end_time" => $blocker['end_time'],
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
