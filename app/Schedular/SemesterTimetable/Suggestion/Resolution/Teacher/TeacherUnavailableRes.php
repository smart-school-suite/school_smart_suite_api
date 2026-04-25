<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use Carbon\Carbon;

class TeacherUnavailableRes extends SuggestionContext implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === TeacherUnavailable::KEY;
    }

    public function resolve($resolution, $params): array
    {
        $pSlot     = $params["perserve_slot"];
        $startTime = Carbon::createFromFormat('H:i', $pSlot['start_time']);
        $endTime   = Carbon::createFromFormat('H:i', $pSlot['end_time']);
        $day       = strtolower($pSlot['day']);

        $context   = ConstraintContext::fromPayload(self::$requestPayload);
        $teachers  = $context->teachers();
        $busySlots = $context->tBusySlotsForDay($day);
        $prefSlots = $context->tPreferredSlotsForDay($day);

        $suggestions = $teachers
            ->filter(function ($teacher) use ($busySlots, $prefSlots, $startTime, $endTime) {
                $teacherId = $teacher['teacher_id'];

                $isBusy = $busySlots
                    ->filter(fn($s) => $s['teacher_id'] === $teacherId)
                    ->some(fn($s) =>
                        $startTime->lessThan(Carbon::createFromFormat('H:i', $s['end_time'])) &&
                        $endTime->greaterThan(Carbon::createFromFormat('H:i', $s['start_time']))
                    );

                if ($isBusy) {
                    return false;
                }

                $teacherPrefs = $prefSlots->filter(fn($s) => $s['teacher_id'] === $teacherId);

                if ($teacherPrefs->isNotEmpty()) {
                    return $teacherPrefs->some(fn($p) =>
                        $startTime->greaterThanOrEqualTo(Carbon::createFromFormat('H:i', $p['start_time'])) &&
                        $endTime->lessThanOrEqualTo(Carbon::createFromFormat('H:i', $p['end_time']))
                    );
                }

                return true;
            })
            ->map(function ($teacher) use ($busySlots) {
                $busyMinutes = $busySlots
                    ->filter(fn($s) => $s['teacher_id'] === $teacher['teacher_id'])
                    ->sum(fn($s) => Carbon::createFromFormat('H:i', $s['start_time'])
                        ->diffInMinutes(Carbon::createFromFormat('H:i', $s['end_time'])));

                return [...$teacher, 'busy_minutes' => $busyMinutes];
            })
            ->sortBy('busy_minutes')
            ->values();

        return $suggestions->all();
    }
}
