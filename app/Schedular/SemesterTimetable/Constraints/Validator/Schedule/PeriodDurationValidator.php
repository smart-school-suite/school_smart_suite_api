<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Schedule;

use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Carbon\Carbon;
class PeriodDurationValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): ?array
    {
        $day      = strtolower($params['day']);
        $start    = Carbon::createFromFormat('H:i', $params['start_time']);
        $end      = Carbon::createFromFormat('H:i', $params['end_time']);
        $actual   = $start->diffInMinutes($end);
        $expected = $context->periodDuration($day);

        if ($actual !== $expected) {
            return [
                'key'               => PeriodDuration::KEY,
                'day'               => $day,
                'expected_duration' => $expected,
                'actual_duration'   => $actual,
            ];
        }

        return null;
    }
}
