<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use Carbon\Carbon;

class TeacherRequestedTimeSlotValidator implements ValidatorInterface
{

    public function check(ConstraintContext $context, array $params): array
    {
        $day = strtolower((string) ($params['day'] ?? ''));
        $teacherId = $params['teacher_id'] ?? null;
        $startRaw = $params['start_time'] ?? null;
        $endRaw   = $params['end_time'] ?? null;

        if ($day === '' || empty($startRaw) || empty($endRaw)) {
            return [];
        }

        $reqStart = Carbon::parse($startRaw);
        $reqEnd   = Carbon::parse($endRaw);

        if ($reqEnd->lessThanOrEqualTo($reqStart)) {
            return [];
        }

        $blockers = [];

        foreach ($context->tRequestedWindowsFor($day) as $trw) {
            $trwStartRaw = $trw['start_time'] ?? null;
            $trwEndRaw   = $trw['end_time'] ?? null;

            if (empty($trwStartRaw) || empty($trwEndRaw)) {
                continue;
            }

            $trwStart = Carbon::parse($trwStartRaw);
            $trwEnd   = Carbon::parse($trwEndRaw);

            if ($trwEnd->lessThanOrEqualTo($trwStart)) {
                continue;
            }

            $overlaps = $reqStart->lt($trwEnd) && $trwStart->lt($reqEnd);

            if ($overlaps) {
                $blockers[] = [
                    'key'        => TeacherRequestedTimeSlot::KEY,
                    'teacher_id' => $trw["teacher_id"],
                    'day'        => $day,
                    'start_time' => $reqStart->format('H:i'),
                    'end_time'   => $reqEnd->format('H:i'),
                ];
            }
        }

        return $blockers;
    }
}
