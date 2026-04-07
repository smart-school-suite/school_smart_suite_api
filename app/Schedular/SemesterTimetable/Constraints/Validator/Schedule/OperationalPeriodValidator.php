<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Carbon\Carbon;

class OperationalPeriodValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): ?array
    {
        $day = strtolower($params['day']);

        $start = Carbon::parse($params['start_time']);
        $end   = Carbon::parse($params['end_time']);

        $opWin   = $context->operationalWindow($day);
        $opStart = Carbon::parse($opWin['start']);
        $opEnd   = Carbon::parse($opWin['end']);

        if ($start->lessThan($opStart) || $end->greaterThan($opEnd)) {
            return [
                'key'        => OperationalPeriod::KEY,
                'day'        => $day,
                'start_time' => $opWin['start'],
                'end_time'   => $opWin['end'],
                "conflict"   => array_filter([
                    "course_id"  => $params["course_id"] ?? null,
                    "start_time" => $params["start_time"] ?? null,
                    "end_time"   => $params["end_time"] ?? null,
                    "day"        => $params["day"] ?? null,
                    "slot_type"  => $params["slot_type"] ?? null,
                    "teacher_id" => $params["teacher_id"] ?? null,
                    "hall_id"    => $params["hall_id"] ?? null,
                ])
            ];
        }

        return [];
    }
}
