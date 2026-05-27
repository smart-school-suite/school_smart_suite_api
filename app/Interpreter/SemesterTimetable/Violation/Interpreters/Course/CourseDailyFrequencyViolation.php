<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Course;

use App\Constant\Violation\SemesterTimetable\Course\CourseDailyFrequency;
use App\Interpreter\SemesterTimetable\DTOs\DiagnosticContext;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use Illuminate\Support\Facades\DB;

class CourseDailyFrequencyViolation extends DiagnosticContext implements ViolationInterpreter
{
    public static function type(): string
    {
        return CourseDailyFrequency::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $currentSchool = self::getSchool();
        $conflict = $blocker->conflict ?? null;
        $evidence = $blocker->evidence ?? null;
        $entity = $blocker->entity;
        $course = DB::table('courses')
            ->where("school_branch_id", $currentSchool->id)
            ->where('id', $entity['course_id'] ?? null)
            ->first();
        $courseTitle = $course->title ?? "unknown course";
        return "Max Daily Course Frequency Violation: The session on {$conflict['day']} at {$conflict['start_time']} to {$conflict['end_time']} for course {$courseTitle} exceeds the maximum daily frequency of {$evidence['max_allowed_per_day']} sessions.";
    }
}
