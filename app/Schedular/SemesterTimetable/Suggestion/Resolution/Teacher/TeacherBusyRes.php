<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use Carbon\Carbon;

class TeacherBusyRes extends SuggestionContext implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === TeacherBusy::KEY;
    }

    public function resolve($resolution, $params): array
    {
        $pSlot      = $params['preserve_slot'];
        $startTime  = $pSlot['start_time'];
        $endTime    = $pSlot['end_time'];
        $day        = strtolower($pSlot['day']);

        $start = Carbon::createFromFormat('H:i', $startTime);
        $end   = Carbon::createFromFormat('H:i', $endTime);

        $context = ConstraintContext::fromPayload(self::$requestPayload);

        $isWithPreference = self::isWithPreference();

        $teachers   = $context->teachers();
        $busySlots  = $context->tBusySlotsForDay($day);
        $prefSlots  = $context->tPreferredSlotsForDay($day);

        $suggestions = $teachers
            ->filter(function ($teacher) use ($context, $day, $start, $end, $busySlots, $prefSlots, $isWithPreference) {
                $teacherId = $teacher['teacher_id'];

                $isBusy = $busySlots
                    ->filter(fn($slot) => $slot['teacher_id'] === $teacherId)
                    ->some(
                        fn($slot) =>
                        $start->lessThan(Carbon::createFromFormat('H:i', $slot['end_time'])) &&
                            $end->greaterThan(Carbon::createFromFormat('H:i', $slot['start_time']))
                    );

                if ($isBusy) {
                    return false;
                }

                if ($isWithPreference) {
                    $teacherPrefs = $prefSlots->filter(fn($slot) => $slot['teacher_id'] === $teacherId);

                    if ($teacherPrefs->isNotEmpty()) {
                        return $teacherPrefs->some(
                            fn($pref) =>
                            $start->greaterThanOrEqualTo(Carbon::createFromFormat('H:i', $pref['start_time'])) &&
                                $end->lessThanOrEqualTo(Carbon::createFromFormat('H:i', $pref['end_time']))
                        );
                    }
                }

                return true;
            })
            ->map(function ($teacher) use ($busySlots) {
                $teacherId   = $teacher['teacher_id'];
                $busyMinutes = $busySlots
                    ->filter(fn($slot) => $slot['teacher_id'] === $teacherId)
                    ->sum(
                        fn($slot) =>
                        Carbon::createFromFormat('H:i', $slot['start_time'])
                            ->diffInMinutes(Carbon::createFromFormat('H:i', $slot['end_time']))
                    );

                return [
                    ...$teacher,
                    'busy_minutes' => $busyMinutes,
                ];
            })
            ->sortBy('busy_minutes')
            ->values();

        return $suggestions->all();
    }
}
