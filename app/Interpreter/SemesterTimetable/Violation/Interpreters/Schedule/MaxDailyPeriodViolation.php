<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;

class MaxDailyPeriodViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'max_daily_period_violation';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence'][0]['violated_max_periods_rule'] ?? null;
        return "
        Max Daily Period Conflict: Adding this session on {$conflict['day']} at
        {$conflict['start_time']} to {$conflict['end_time']} would exceed the maximum allowed periods {$evidence['max_allowed_per_day']} on {$evidence['day']} if scheduled with the existing sessions";
    }
}
