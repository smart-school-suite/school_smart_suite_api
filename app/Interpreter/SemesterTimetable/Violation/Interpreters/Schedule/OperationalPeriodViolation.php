<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;

class OperationalPeriodViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'operational_period_violation';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence']['conflicting_assignment'] ?? null;
        return "Operational Period Violation: The requested session on {$conflict['day']} from {$conflict['start_time']} to {$conflict['end_time']} falls outside the operational period.";
    }
}
