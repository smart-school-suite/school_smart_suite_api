<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Hall;

use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;

class HallRequestedTimeSlotViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return 'hall_requested_time_slot_violation';
    }

    public function explain(array $blocker): string
    {
        $entity = $blocker['entity']['preferred_slot'] ?? null;
        $conflict = $blocker['conflict']['requested_slot'] ?? null;
        return "Hall Requested Time Slot Violation: The requested session on {$conflict['day']} from {$conflict['start_time']} to {$conflict['end_time']} conflicts with an existing assignment in the same hall on {$entity['day']} from {$entity['start_time']} to {$entity['end_time']}.";
    }
}
