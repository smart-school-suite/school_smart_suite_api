<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodBlocker;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use Carbon\Carbon;

class RequestedFreePeriodRes extends SuggestionContext implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === RequestedFreePeriodBlocker::KEY || $type === RequestedFreePeriodConstraint::KEY;
    }

    public function resolve($resolution, $params): array
    {
        $pSlot  = $params['preserve_slot'];
        $pStart = $pSlot['start_time'];
        $pEnd   = $pSlot['end_time'];
        $pDay   = strtolower($pSlot['day']);

        $intentDetails = $resolution->meta;
        $iStartTime    = $intentDetails['start_time'];
        $intentStart   = Carbon::createFromFormat('H:i', $iStartTime);

        // 1. Build list of all regular slots on the same day, excluding the preserved slot
        $slots = collect(self::getTimetableGrid())
            ->filter(fn($slot) =>
                $slot->type === GridSlotDTO::TYPE_REGULAR &&
                $slot->day === $pDay &&
                !($slot->start_time === $pStart && $slot->end_time === $pEnd)
            )
            ->map(fn($slot) => [
                'day'        => $slot->day,
                'start_time' => $slot->start_time,
                'end_time'   => $slot->end_time,
            ]);

        // 2. Rank by proximity to the user's intent start time
        return $slots->sortBy(fn($slot) =>
            Carbon::createFromFormat('H:i', $slot['start_time'])
                ->diffInMinutes($intentStart, absolute: true)
        )->values()->all();
    }
}
