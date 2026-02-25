<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Course;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Courses;

class CourseMaxDailyFrequencyViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'max_course_daily_frequency_violation';
    }

    public function explain(array $blocker): string
    {
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence'][0]['violated_daily_frequency_rule'] ?? null;
        $course = Courses::find($evidence['course_id'] ?? null);
        return "Max Daily Course Frequency Violation: The session on {$conflict['day']} at
        {$conflict['start_time']} to {$conflict['end_time']} for course {$course->code} exceeds the maximum daily frequency of {$evidence['max_allowed_per_day']} sessions.";
    }
}
