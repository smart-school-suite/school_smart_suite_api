<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class RequestedFreePeriodViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return RequestedFreePeriod::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $entity = $blocker->entity ?? null;
        $conflict = $blocker->conflict ?? null;

        return "Free Period Conflict: The requested session on ";
    }
}
