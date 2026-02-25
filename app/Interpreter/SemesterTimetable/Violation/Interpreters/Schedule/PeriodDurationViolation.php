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
        $entity = $blocker['entity'] ?? null;
        $conflict = $blocker['conflict'] ?? null;
        $evidence = $blocker['evidence'][0]['violated_period_duration_rule'] ?? null;

        return "
        Period Duration Conflict: The requested session on {$conflict['requested_slot']['day']} at
        {$conflict['requested_slot']['start_time']} to {$conflict['requested_slot']['end_time']} has a duration of {$conflict['requested_duration']} minutes, which violates the allowed duration of {$evidence['allowed_duration']} minutes on {$evidence['day']} if scheduled";
    }
}
