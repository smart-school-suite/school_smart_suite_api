<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodBlocker;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ResolutionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OperationalPeriodRes extends SuggestionContext implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === OperationalPeriodConstraint::KEY || $type === OperationalPeriodBlocker::KEY;
    }

    public function resolve(ResolutionDTO $resolution, array $params): array
    {
        return self::isHardScenario()
            ? $this->resolveHard($resolution, $params)
            : $this->resolveSoft($resolution, $params);
    }

    protected function resolveSoft(ResolutionDTO $resolution, array $params): array
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

    protected function resolveHard(ResolutionDTO $resolution, array $params): array
    {
        $pSlot = $params['preserve_slot'];
        $pEnd = Carbon::parse($pSlot['end_time']);
        $pDay = strtolower($pSlot['day']);

        // User intent break that failed constraint
        $intent = $resolution->meta["constraint_failed"];
        $intentStart = Carbon::parse($intent["start_time"]);
        return [
            [
                "id" => Str::uuid()->toString(),
                "day" => $pDay,
                "start_time" => $intentStart->format('H:s'),
                "end_time" => $pEnd->format('H:s')
            ]
        ];
    }
}
