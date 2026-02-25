<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Models\Teacher;

class TeacherBusy implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'teacher_busy';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence']['conflicting_assignment'] ?? null;
        $teacher = Teacher::find($entity['teacher_id'] ?? null);
        return "
        Teacher Busy: {$teacher->name} is already assigned to a session on {$evidence['day']} from
        {$evidence['start_time']} to {$evidence['end_time']}, which conflicts with the requested session on
        {$conflict['day']} from {$conflict['start_time']} to {$conflict['end_time']}.";
    }
}
