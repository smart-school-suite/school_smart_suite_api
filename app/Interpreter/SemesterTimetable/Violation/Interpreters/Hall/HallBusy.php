<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Hall;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;

class HallBusy implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'hall_busy';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence']['conflicting_assignment'] ?? null;
        return "Hall Busy Violation: The requested session on {$conflict['day']} from {$conflict['start_time']} to {$conflict['end_time']} conflicts with an existing assignment in the same hall on
        {$evidence['day']} from {$evidence['start_time']} to {$evidence['end_time']}.";
    }
}
