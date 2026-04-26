<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodBlocker;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use Carbon\Carbon;
class OperationalPeriodRes implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === OperationalPeriodConstraint::KEY || OperationalPeriodBlocker::KEY;
    }

    public function resolve($resolution, $params): array
    {
        $pSlot  = $params['preserve_slot'];
        $pStart = $pSlot['start_time'];
        $pEnd   = $pSlot['end_time'];
        $pDay   = strtolower($pSlot['day']);
        return  [
            "day" => $pDay,
            "start_time" => $pStart,
            "end_time" => $pEnd
        ];
    }
}
