<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use Carbon\Carbon;

class BreakPeriodValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): ?array
    {
        $day   = strtolower($params['day']);
        $start = Carbon::parse($params['start_time']);
        $end   = Carbon::parse($params['end_time']);

        $breakWin = $context->breakWindow($day);

        if ($breakWin === null) {
            return [];
        }

        $breakStart = Carbon::parse($breakWin['start']);
        $breakEnd   = Carbon::parse($breakWin['end']);

        if ($start->lessThan($breakEnd) && $end->greaterThan($breakStart)) {
            return [
                'key'        => BreakPeriod::KEY,
                'day'        => $day,
                'start_time' => $breakWin['start'],
                'end_time'   => $breakWin['end'],
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

        return [];
    }
}
