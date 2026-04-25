<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use Carbon\Carbon;
class HallBusyRes extends SuggestionContext implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === HallBusy::KEY;
    }

    public function resolve($resolution, $params): array
    {
        $pSlot     = $params['preserve_slot'];
        $startTime = Carbon::createFromFormat('H:i', $pSlot['start_time']);
        $endTime   = Carbon::createFromFormat('H:i', $pSlot['end_time']);
        $day       = strtolower($pSlot['day']);

        $context   = ConstraintContext::fromPayload(self::$requestPayload);
        $allHalls  = $context->halls();
        $busySlots = $context->hBusySlotsFor($day);

        $availableHalls = $allHalls->filter(function ($hall) use ($busySlots, $startTime, $endTime) {
            $hallId = $hall['hall_id'];

            $isBusy = $busySlots
                ->filter(fn($slot) => $slot['hall_id'] === $hallId)
                ->some(function ($slot) use ($startTime, $endTime) {
                    $slotStart = Carbon::createFromFormat('H:i', $slot['start_time']);
                    $slotEnd   = Carbon::createFromFormat('H:i', $slot['end_time']);

                    return $startTime->lessThan($slotEnd) && $endTime->greaterThan($slotStart);
                });

            return !$isBusy;
        })->values();

        return $availableHalls->all();
    }
}
