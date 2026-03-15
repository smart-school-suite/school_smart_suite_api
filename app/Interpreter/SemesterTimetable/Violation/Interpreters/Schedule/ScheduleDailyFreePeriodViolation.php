<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyFreePeriod;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;

class ScheduleDailyFreePeriodViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return ScheduleDailyFreePeriod::KEY;
    }

    public function explain(array $blocker): string
    {
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        $evidence = $blocker['evidence']['violated_free_periods_rule'] ?? null;
        return "Max Free Period Per Day Conflict: Adding another free period on {$conflict['day']} at {$conflict['start_time']} to {$conflict['end_time']} would exceed the maximum allowed free periods {$evidence['max_allowed_free_periods']} on {$evidence['day']} if scheduled with the existing sessions";
    }
}
