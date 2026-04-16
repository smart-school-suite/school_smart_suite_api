<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot as TeacherRequestedTimeSlotBlockerConstant;
use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot as TeacherRequestedTimeSlotConstraintConstant;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;

class TeacherRequestedTimeSlotBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return TeacherRequestedTimeSlotBlockerConstant::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = TeacherRequestedTimeSlotBlockerConstant::KEY;
        $violation->id = app(GenerateId::class)->generateId([
            "type" => TeacherRequestedTimeSlotConstraintConstant::KEY,
            "start_time" => $blocker["start_time"],
            "end_time" => $blocker["end_time"],
            "day" => $blocker["day"],
            "teacher_id" => $blocker["teacher_id"]
        ]);
        $violation->entity = [
            "type" => TeacherRequestedTimeSlotConstraintConstant::KEY,
            "start_time" => $blocker["start_time"],
            "end_time" => $blocker["end_time"],
            "day" => $blocker["day"],
            "teacher_id" => $blocker["teacher_id"]
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
