<?php

namespace App\Schedular\SemesterTimetable\Helpers;

use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\TimetableContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GetTeacherByWorkLoadScore extends TimetableContext
{
    public function getTeacherByAvailabilityScore(array $requestPayload, array $params, State $state): Collection
    {
        $day   = strtolower($params['day']);
        $start = Carbon::createFromFormat('H:i', $params['start_time']);
        $end   = Carbon::createFromFormat('H:i', $params['end_time']);

        $context = ConstraintContext::fromPayload($requestPayload);

        return $context->teachers()
            ->filter(function ($teacher) use ($context, $day, $start, $end) {
                $teacherId = $teacher['teacher_id'];

                $isBusy = $context->tBusySlotsFor($teacherId, $day)
                    ->some(
                        fn($slot) =>
                        $start->lessThan(Carbon::createFromFormat('H:i', $slot['end_time'])) &&
                            $end->greaterThan(Carbon::createFromFormat('H:i', $slot['start_time']))
                    );

                if ($isBusy) {
                    return false;
                }

                if (TimetableContext::isWithoutPreference()) {
                    return true;
                }

                $prefs = $context->tPreferredSlotsFor($teacherId, $day);

                if ($prefs->isEmpty()) {
                    return true;
                }

                return $prefs->some(
                    fn($pref) =>
                    $start->greaterThanOrEqualTo(Carbon::createFromFormat('H:i', $pref['start_time'])) &&
                        $end->lessThanOrEqualTo(Carbon::createFromFormat('H:i', $pref['end_time']))
                );
            })
            ->map(function ($teacher) use ($context, $day) {
                $teacherId     = $teacher['teacher_id'];
                $busyMinutes   = $this->sumBusyMinutes($context->tBusySlotsFor($teacherId, $day));

                return [
                    ...$teacher,
                    'busy_minutes' => $busyMinutes,
                ];
            })
            ->sortBy('busy_minutes')
            ->values();
    }

    public function getTeacherByBusyScore(array $requestPayload, array $params, State $state): Collection
    {
        $day   = strtolower($params['day']);
        $start = Carbon::createFromFormat('H:i', $params['start_time']);
        $end   = Carbon::createFromFormat('H:i', $params['end_time']);

        $context = ConstraintContext::fromPayload($requestPayload);

        return $context->teachers()
            ->filter(function ($teacher) use ($context, $day, $start, $end) {
                $isBusy = $context->tBusySlotsFor($teacher['teacher_id'], $day)
                    ->some(
                        fn($slot) =>
                        $start->lessThan(Carbon::createFromFormat('H:i', $slot['end_time'])) &&
                            $end->greaterThan(Carbon::createFromFormat('H:i', $slot['start_time']))
                    );

                return !$isBusy;
            })
            ->map(function ($teacher) use ($context, $day) {
                $busyMinutes = $this->sumBusyMinutes($context->tBusySlotsFor($teacher['teacher_id'], $day));

                return [
                    ...$teacher,
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
