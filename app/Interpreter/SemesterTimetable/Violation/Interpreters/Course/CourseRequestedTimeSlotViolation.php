<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Course;

use App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Courses;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class CourseRequestedTimeSlotViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return CourseRequestedSlot::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $entity = $blocker->entity ?? null;
        $conflict = $blocker->conflict ?? null;
        $courseEntity = Courses::find($entity['course_id'] ?? null);
        $conflictingCourse = Courses::find($conflict['course_id'] ?? null);

        return "Requested Course Time Slot Violation: The requested session on ";
    }
}
