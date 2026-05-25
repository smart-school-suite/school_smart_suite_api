<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Normalization\Helpers;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use Carbon\Carbon;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Illuminate\Support\Collection;
class Methods extends SuggestionContext
{
    public function hasAvailableTeacher(
        array             $slot,
        ConstraintContext  $context,
        string            $day,
        bool              $isWithPreference
    ): bool {
        $start = Carbon::createFromFormat('H:i', $slot['start_time']);
        $end   = Carbon::createFromFormat('H:i', $slot['end_time']);

        return $context->teachers()->some(function ($teacher) use ($context, $day, $start, $end, $isWithPreference) {
            $teacherId = $teacher['teacher_id'];

            $isBusy = $context->tBusySlotsFor($teacherId, $day)
                ->some(
                    fn($busy) =>
                    $start->lessThan(Carbon::createFromFormat('H:i', $busy['end_time'])) &&
                        $end->greaterThan(Carbon::createFromFormat('H:i', $busy['start_time']))
                );

            if ($isBusy) {
                return false;
            }

            // preference check skipped when flag is off
            if (!$isWithPreference) {
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
        });
    }

    public function hasAvailableHall(array $slot, ConstraintContext $context, string $day): bool
    {
        $start = Carbon::createFromFormat('H:i', $slot['start_time']);
        $end   = Carbon::createFromFormat('H:i', $slot['end_time']);

        return $context->halls()->some(
            fn($hall) =>
            !$context->hBusySlotsForHallIdDay($hall['hall_id'], $day)
                ->some(
                    fn($busy) =>
                    $start->lessThan(Carbon::createFromFormat('H:i', $busy['end_time'])) &&
                        $end->greaterThan(Carbon::createFromFormat('H:i', $busy['start_time']))
                )
        );
    }

    public function buildCandidatePool(string $day): Collection
    {
        return collect(self::$timetableGrid)
            ->filter(
                fn($slot) =>
                $slot->day  === $day &&
                    $slot->type === GridSlotDTO::TYPE_REGULAR
            )
            ->map(fn($slot) => [
                'day'        => $slot->day,
                'start_time' => $slot->start_time,
                'end_time'   => $slot->end_time,
            ])
            ->values();
    }
}
