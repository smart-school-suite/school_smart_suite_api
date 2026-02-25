<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Course;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\Teacher;

class RequiredJointCourseViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'course_requested_time_slot_violation';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity']['fixed_joint_course_slot'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $course  = Courses::find($entity['course_id'] ?? null);
        $teacher = Teacher::find($entity['teacher_id'] ?? null);
        $hall = Hall::find($entity['hall_id'] ?? null);
        $conflictingCourse = Courses::find($conflict['course_id'] ?? null);

        return "Required Joint Course Violation: The fixed joint course session on {$entity['day']} at
        {$entity['start_time']} to {$entity['end_time']} for course {$course->course_title} with teacher {$teacher->name} in hall {$hall->name} conflicts with the requested session on {$conflict['day']} at
        {$conflict['start_time']} to {$conflict['end_time']} for course {$conflictingCourse->course_title}.";
    }
}
