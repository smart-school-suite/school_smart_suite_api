<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course;

use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse as RequiredJointCourseBlockerConstant;
use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse as RequiredJointCourseConstraintConstant;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;

class RequiredJointCourseBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return RequiredJointCourseBlockerConstant::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = self::type();
        $violation->id = app(GenerateId::class)->generateId([
            "type" => RequiredJointCourseConstraintConstant::KEY,
            "course_id" => $blocker['course_id'],
            "hall_id" => $blocker['hall_id'],
            "day" => $blocker['day'],
            "start_time" => $blocker['start_time'],
            "end_time" => $blocker['end_time'],
            "teacher_id" => $blocker['teacher_id']
        ]);
        $violation->entity = [
            "type" => RequiredJointCourseConstraintConstant::KEY,
            "course_id" => $blocker['course_id'],
            "hall_id" => $blocker['hall_id'],
            "day" => $blocker['day'],
            "start_time" => $blocker['start_time'],
            "end_time" => $blocker['end_time'],
            "teacher_id" => $blocker['teacher_id']
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
