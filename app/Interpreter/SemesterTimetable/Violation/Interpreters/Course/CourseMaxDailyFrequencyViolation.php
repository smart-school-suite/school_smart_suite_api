<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Course;

use App\Interpreter\SemesterTimetable\DTOs\DiagnosticContext;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use Illuminate\Support\Facades\DB;

class CourseMaxDailyFrequencyViolation extends DiagnosticContext implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'max_course_daily_frequency_violation';
    }

    public function explain(array $blocker): string
    {
        $currentSchool = self::getSchool();
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence'][0]['violated_daily_frequency_rule'] ?? null;
        $entity = $blocker['entity'];
        $course = DB::table('courses')
            ->where("school_branch_id", $currentSchool->id)
            ->where('id', $entity['course_id'] ?? null)
            ->first();
        $courseTitle = $course->title ?? "unknown course";
        return "Max Daily Course Frequency Violation: The session on {$conflict['day']} at {$conflict['start_time']} to {$conflict['end_time']} for course {$courseTitle} exceeds the maximum daily frequency of {$evidence['max_allowed_per_day']} sessions.";
    }
}
