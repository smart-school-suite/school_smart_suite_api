<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Course;

use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\Teacher;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class RequiredJointCourseViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return RequiredJointCourse::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $entity = $blocker->entity ?? null;
        $conflict = $blocker->conflict ?? null;
        $course  = Courses::find($entity['course_id'] ?? null);
        $teacher = Teacher::find($entity['teacher_id'] ?? null);
        $hall = Hall::find($entity['hall_id'] ?? null);
        $conflictingCourse = Courses::find($conflict['course_id'] ?? null);

        return "Required Joint Course Violation: The fixed joint course session on";
    }
}
