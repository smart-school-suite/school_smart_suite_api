<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;

class RequestedFreePeriodViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'requested_free_period_violation';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity']['protected_free_period'] ?? null;
        $conflict = $blocker['conflict']['attempted_slot'] ?? null;

        return "Free Period Conflict: The requested session on {$conflict['day']} at
        {$conflict['start_time']} to {$conflict['end_time']} conflicts with a free period on {$entity['day']} from {$entity['start_time']} to {$entity['end_time']}";
    }
}
