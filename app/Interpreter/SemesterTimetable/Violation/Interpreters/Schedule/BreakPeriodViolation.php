<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;

class BreakPeriodViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return BreakPeriod::KEY;
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;

        return "Break Period Conflict: Break period scheduled on {$entity['day']} at {$entity['start_time']} to {$entity['end_time']} conflicted with the attempted slot on {$conflict['day']} at {$conflict['start_time']} to {$conflict['end_time']}.";
    }
}
