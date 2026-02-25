<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Courses;
use App\Models\Teacher;

class TeacherCourseViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'teacher_course_violation';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence']['conflicting_assignment'] ?? null;
        $teacher = Teacher::find($entity['teacher_id'] ?? null);
        $course =  Courses::find($entity['course_id'] ?? null);
        return "Teacher Course Mismatch: {$course->name} is assigned to teacher {$teacher->name}, but the requested session is for a different course.";
    }
}
