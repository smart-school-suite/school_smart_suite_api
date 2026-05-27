<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallRequestedTimeSlot;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class HallRequestedTimeSlotViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return HallRequestedTimeSlot::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $entity = $blocker->entity ?? null;
        $conflict = $blocker->conflict ?? null;
        return "Hall Requested Time Slot Violation: ";
    }
}
