<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Teacher;
class TeacherInsufficiency implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'teacher_insufficiency';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence']['conflicting_assignment'] ?? null;
        $teacher = Teacher::find($entity['teacher_id'] ?? null);
        return "Teacher Insufficiency Violation: Only {$evidence['available_teachers']} teachers are available for the requested session on {$conflict['day']} from
        {$conflict['start_time']} to {$conflict['end_time']}, which is insufficient to meet the required number of teachers.";
    }
}
