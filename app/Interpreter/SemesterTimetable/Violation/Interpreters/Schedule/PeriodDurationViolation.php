<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;

class PeriodDurationViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'period_duration_violation';
    }

    public function explain(array $blocker): string
    {
        $conflict = $blocker['conflict'] ?? null;
        $requestedSlot = $blocker['conflict']['requested_slot'];
        $evidence = $blocker['evidence']['violated_duration_rule'] ?? null;

        return "Period Duration Conflict: The requested session on {$requestedSlot['day']} at {$requestedSlot['start_time']} to {$requestedSlot['end_time']} has a duration of {$conflict['duration_minutes']} minutes, which violates the allowed duration of {$evidence['allowed_minutes']} minutes on {$evidence['day']} if scheduled";
    }
}
