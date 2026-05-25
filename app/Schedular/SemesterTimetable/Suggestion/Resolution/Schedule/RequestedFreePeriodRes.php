<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodBlocker;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use Carbon\Carbon;
use Illuminate\Support\Str;
class RequestedFreePeriodRes extends SuggestionContext implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === RequestedFreePeriodBlocker::KEY || $type === RequestedFreePeriodConstraint::KEY;
    }

    public function resolve(object $resolution, array $params): array
    {
        $pSlot  = $params['preserve_slot'];
        $pStart = $pSlot['start_time'];
        $pEnd   = $pSlot['end_time'];
        $pDay   = strtolower($pSlot['day']);

        $intentDetails = $resolution->meta;
        $iStartTime    = $intentDetails['start_time'];
        $intentStart   = Carbon::createFromFormat('H:i', $iStartTime);

        $slots = collect(self::getTimetableGrid())
            ->filter(fn($slot) =>
                $slot->type === GridSlotDTO::TYPE_REGULAR &&
                $slot->day === $pDay &&
                !($slot->start_time === $pStart && $slot->end_time === $pEnd)
            )
            ->map(fn($slot) => [
                "id" => Str::uuid()->toString(),
                'day'        => $slot->day,
                'start_time' => $slot->start_time,
                'end_time'   => $slot->end_time,
            ]);

        return $slots->sortBy(fn($slot) =>
            Carbon::createFromFormat('H:i', $slot['start_time'])
                ->diffInMinutes($intentStart, absolute: true)
        )->values()->all();
    }
}
