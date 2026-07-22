<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class PeriodDurationViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return PeriodDuration::KEY;
    }
    public function explain(BlockerDTO $blocker): string
    {
        $conflict = $blocker->conflict ?? null;
        $evidence = $blocker->evidence ?? null;

        return "Period Duration Conflict: The requested session on";
    }
}
