<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherWeeklyHours;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Teacher;
class TeacherWeeklyHourViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return TeacherWeeklyHours::KEY;
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence']['violated_teacher_weekly_hours_rule'] ?? null;
        $teacher = Teacher::find($entity['teacher_id'] ?? null);
        return "Max Teacher Weekly Hours Violation: Scheduling A Session from {$conflict['start_time']} to {$conflict['end_time']} on {$conflict['day']} for teacher {$teacher->name} exceeds the maximum allowed weekly hours {$evidence['max_allowed_per_week']}";
    }
}
