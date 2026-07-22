<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyPeriod;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class ScheduleDailyPeriodViolation implements ViolationInterpreter
{
    public static function type(): string
    {
        return ScheduleDailyPeriod::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $conflict = $blocker->conflict ?? null;
        $evidence = $blocker->evidence ?? null;
        return "Max Daily Period Conflict: Adding this session on ";
    }
}
