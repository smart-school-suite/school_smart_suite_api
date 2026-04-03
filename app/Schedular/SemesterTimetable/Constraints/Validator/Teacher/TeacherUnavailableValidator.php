<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Carbon\Carbon;

class TeacherUnavailableValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): array
    {
        $teacherId = $params['teacher_id'];
        $day       = strtolower($params['day']);
        $start     = Carbon::createFromFormat('H:i', $params['start_time']);
        $end       = Carbon::createFromFormat('H:i', $params['end_time']);

        $prefs = array_filter(
            $context->tPreferredSlots()->toArray(),
            fn($p) => $p['teacher_id'] === $teacherId
        );

        if (empty($prefs)) {
            return [];
        }

        foreach ($prefs as $pref) {
            if (strtolower($pref['day']) !== $day) {
                continue;
            }

            $prefStart = Carbon::createFromFormat('H:i', $pref['start_time']);
            $prefEnd   = Carbon::createFromFormat('H:i', $pref['end_time']);

            if ($start->greaterThanOrEqualTo($prefStart) && $end->lessThanOrEqualTo($prefEnd)) {
                return [];
            }
        }

        return [
            'key'        => TeacherUnavailable::KEY,
            'teacher_id' => $teacherId,
            'day'        => $day,
            'start_time' => $params['start_time'],
            'end_time'   => $params['end_time'],
        ];
    }
}
