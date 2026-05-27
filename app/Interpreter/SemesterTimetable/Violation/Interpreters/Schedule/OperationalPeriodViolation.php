<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class OperationalPeriodViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return OperationalPeriod::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $entity = $blocker->entity ?? null;
        $conflict = $blocker->conflict ?? null;
        $evidence = $blocker->evidence ?? null;
        return "Operational Period Violation: The requested session on";
    }
}
