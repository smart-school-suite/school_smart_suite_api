<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Teacher;

class TeacherUnavailable implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'teacher_unavailable';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence']['conflicting_assignment'] ?? null;
        $teacher = Teacher::find($entity['teacher_id'] ?? null);

        return "Teacher Unavailable: {$teacher->name} is not available on {$conflict['day']} from
        {$conflict['start_time']} to {$conflict['end_time']} as it is not within his/her preferred teaching time.";
    }
}
