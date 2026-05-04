<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration  as PeriodDurationConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationViolation;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use Illuminate\Support\Str;

class SchedulePeriodDurationRes extends SuggestionContext implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === PeriodDurationViolation::KEY || $type === PeriodDurationConstraint::KEY;
    }

    public function resolve(object $resolution, array $params): array
    {
        $pSlot  = $params['preserve_slot'];
        $pStart = $pSlot['start_time'];
        $pEnd   = $pSlot['end_time'];
        $pDay   = strtolower($pSlot['day']);
        return  [
            [
                "id" => Str::uuid()->toString(),
                "day" => $pDay,
                "start_time" => $pStart,
                "end_time" => $pEnd
            ]
        ];
    }
}
