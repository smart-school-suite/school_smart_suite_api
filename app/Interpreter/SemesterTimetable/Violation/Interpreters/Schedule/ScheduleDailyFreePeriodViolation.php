<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyFreePeriod;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class ScheduleDailyFreePeriodViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return ScheduleDailyFreePeriod::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $conflict = $blocker->conflict ?? null;
        $evidence = $blocker->evidence ?? null;
        return "Max Free Period Per Day Conflict: Adding another free period ";
    }
}
