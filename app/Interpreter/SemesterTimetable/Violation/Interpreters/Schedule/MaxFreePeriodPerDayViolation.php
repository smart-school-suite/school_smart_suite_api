<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;

class MaxFreePeriodPerDayViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'max_free_period_per_day_violation';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence'][0]['violated_max_free_periods_rule'] ?? null;
        return "
        Max Free Period Per Day Conflict: Adding another free period on {$conflict['day']} at
        {$conflict['start_time']} to {$conflict['end_time']} would exceed the maximum allowed free periods {$evidence['max_allowed_free_periods']} on {$evidence['day']} if scheduled with the existing sessions";
    }
}
