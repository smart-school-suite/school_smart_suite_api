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
        $start     = Carbon::parse($params['start_time']);
        $end       = Carbon::parse($params['end_time']);

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
            "conflict" => array_filter([
                "course_id" => $params["course_id"] ?? null,
                "hall_id" => $params["hall_id"] ?? null,
                "slot_type" => $params["slot_type"] ?? null,
                "teacher_id" => $params["teacher_id"] ?? null,
                "day" => $params["day"] ?? null,
                "start_time" => $params["start_time"] ?? null,
                "end_time" => $params["end_time"] ?? null,
            ])
        ];
    }
}
