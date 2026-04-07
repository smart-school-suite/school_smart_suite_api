<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Schedule;

use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Carbon\Carbon;

class PeriodDurationValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): array
    {
        $day      = strtolower($params['day']);
        $start    = Carbon::parse($params['start_time']);
        $end      = Carbon::parse($params['end_time']);
        $actual   = (float) $start->diffInMinutes($end);
        $expected = (float) $context->periodDuration($day);

        if ($actual !== $expected) {
            return [
                'key'               => PeriodDuration::KEY,
                'day'               => $day,
                'expected_duration' => $expected,
                'actual_duration'   => $actual,
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
