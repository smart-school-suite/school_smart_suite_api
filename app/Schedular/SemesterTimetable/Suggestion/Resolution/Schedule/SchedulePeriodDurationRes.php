<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration  as PeriodDurationConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationViolation;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;

class SchedulePeriodDurationRes extends SuggestionContext implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === PeriodDurationViolation::KEY || PeriodDurationConstraint::KEY;
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
