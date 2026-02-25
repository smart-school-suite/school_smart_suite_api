<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Course;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Courses;

class CourseRequestedTimeSlotViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'course_requested_time_slot_violation';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity']['preferred_slot'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $courseEntity = Courses::find($entity['course_id'] ?? null);
        $conflictingCourse = Courses::find($conflict['course_id'] ?? null);

        return "Requested Course Time Slot Violation: The requested session on {$conflict['day']} at
        {$conflict['start_time']} to {$conflict['end_time']} for course {$courseEntity->course_title}
         conflicts with an existing session for course {$conflictingCourse->course_title} on {$conflict['day']}
         at {$conflict['start_time']} to {$conflict['end_time']}.";
    }
}
