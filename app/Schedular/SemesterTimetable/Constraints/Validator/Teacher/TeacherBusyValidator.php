<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Carbon\Carbon;

class TeacherBusyValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): array
    {
        $day   = strtolower($params['day']);
        $start = Carbon::createFromFormat('H:i', $params['start_time']);
        $end   = Carbon::createFromFormat('H:i', $params['end_time']);
        $blockers = [];

        foreach ($context->tBusySlotsFor($params['teacher_id'], $day) as $tbw) {
            $tbwStart = Carbon::createFromFormat('H:i', $tbw['start_time']);
            $tbwEnd   = Carbon::createFromFormat('H:i', $tbw['end_time']);

            if ($start->lessThan($tbwEnd) && $end->greaterThan($tbwStart)) {
                $blockers[] = [
                    'key'        => TeacherBusy::KEY,
                    'day'        => $day,
                    'teacher_id' => $tbw['teacher_id'] ?? null,
                    'start_time' => $tbw['start_time'],
                    'end_time'   => $tbw['end_time'],
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

        return $blockers;
    }
}
