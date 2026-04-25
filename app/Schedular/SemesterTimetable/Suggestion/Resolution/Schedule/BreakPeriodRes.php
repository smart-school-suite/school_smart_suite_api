<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodBlocker;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;

class BreakPeriodRes implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === BreakPeriodBlocker::KEY || BreakPeriodConstraint::KEY;
    }

    public function resolve($resolution, $params): array
    {
        throw new \Exception('Not implemented');
    }
}
