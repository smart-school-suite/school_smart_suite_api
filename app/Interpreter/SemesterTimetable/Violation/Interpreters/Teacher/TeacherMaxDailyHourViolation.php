<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Teacher;

class TeacherMaxDailyHourViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'max_teacher_daily_hours_violation';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence']['conflicting_assignment'] ?? null;
        $teacher = Teacher::find($entity['teacher_id'] ?? null);
        return "Max Teacher Daily Hours Violation: Sheduling A Session from {$conflict['start_time']} to
        {$conflict['end_time']} on {$conflict['day']} for teacher {$teacher->name} exceeds the maximum
        allowed daily hours {$evidence['max_allowed_per_day']}";
    }
}
