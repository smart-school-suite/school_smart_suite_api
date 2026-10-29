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
        $pDay = strtolower($pSlot['day']);

        // Operational period that blocks the kept constraint
        $intent = $resolution->meta["constraint_failed"];
        $start = Carbon::parse($intent["start_time"]);
        $end = Carbon::parse($intent["end_time"]);

        if (isset($pSlot['start_time'], $pSlot['end_time'])) {
            // Kept slot (break, joint course): widen the window to cover it
            $start = $start->min(Carbon::parse($pSlot['start_time']));
            $end = $end->max(Carbon::parse($pSlot['end_time']));
        } else {
            // Kept period duration: the window must fit at least one period
            $duration = (int) ($pSlot['period_duration'] ?? $pSlot['duration_minutes'] ?? 0);
            $end = $end->max($start->copy()->addMinutes($duration));
        }

        return [
            [
                "id" => Str::uuid()->toString(),
                "day" => $pDay,
                "start_time" => $start->format('H:i'),
                "end_time" => $end->format('H:i')
            ]
        ];
    }
}
