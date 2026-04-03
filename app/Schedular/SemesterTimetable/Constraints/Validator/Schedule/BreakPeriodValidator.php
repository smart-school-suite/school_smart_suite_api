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
        $start = Carbon::createFromFormat('H:i', $params['start_time']);
        $end   = Carbon::createFromFormat('H:i', $params['end_time']);

        $breakWin = $context->breakWindow($day);

        if ($breakWin === null) {
            return null;
        }

        $breakStart = Carbon::createFromFormat('H:i', $breakWin['start']);
        $breakEnd   = Carbon::createFromFormat('H:i', $breakWin['end']);

        if ($start->lessThan($breakEnd) && $end->greaterThan($breakStart)) {
            return [
                'key'        => BreakPeriod::KEY,
                'day'        => $day,
                'start_time' => $breakWin['start'],
                'end_time'   => $breakWin['end'],
            ];
        }

        return null;
    }
}
