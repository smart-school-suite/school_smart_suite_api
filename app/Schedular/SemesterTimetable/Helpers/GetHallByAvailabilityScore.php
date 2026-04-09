<?php

namespace App\Schedular\SemesterTimetable\Helpers;

use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Core\State;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GetHallByAvailabilityScore
{
    public function getHallByAvailabilityScore(array $requestPayload, array $params, State $state): Collection
    {
        $day   = strtolower($params['day']);
        $start = Carbon::createFromFormat('H:i', $params['start_time']);
        $end   = Carbon::createFromFormat('H:i', $params['end_time']);

        $context = ConstraintContext::fromPayload($requestPayload);

        return $context->halls()
            ->filter(function ($hall) use ($context, $day, $start, $end) {
                $isBusy = $context->hBusySlotsFor(strtolower($day))
                    ->filter(
                        fn($s) => $s['hall_id'] === $hall['hall_id']
                    )->values()
                    ->some(
                        fn($slot) =>
                        $start->lessThan(Carbon::createFromFormat('H:i', $slot['end_time'])) &&
                            $end->greaterThan(Carbon::createFromFormat('H:i', $slot['start_time']))
                    );

                return !$isBusy;
            })
            ->map(function ($hall) use ($context, $day) {
                $busyMinutes = $this->sumBusyMinutes($context->hBusySlotsFor(strtolower($day))
                    ->filter(
                        fn($s) => $s['hall_id'] === $hall['hall_id']
                    )->values());

                return [
                    ...$hall,
                    'busy_minutes' => $busyMinutes,
                ];
            })
            ->sortBy('busy_minutes')
            ->values();
    }
    private function sumBusyMinutes(Collection $busySlots): int
    {
        return $busySlots->sum(
            fn($slot) =>
            Carbon::createFromFormat('H:i', $slot['start_time'])
                ->diffInMinutes(Carbon::createFromFormat('H:i', $slot['end_time']))
        );
    }
}
