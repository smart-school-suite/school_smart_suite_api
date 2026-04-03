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
        $day   = strtolower($params['day']);
        $start = Carbon::createFromFormat('H:i', $params['start_time']);
        $end   = Carbon::createFromFormat('H:i', $params['end_time']);

        $opWin      = $context->operationalWindow($day);
        $opStart    = Carbon::createFromFormat('H:i', $opWin['start']);
        $opEnd      = Carbon::createFromFormat('H:i', $opWin['end']);

        if ($start->lessThan($opStart) || $end->greaterThan($opEnd)) {
            return [
                'key'        => OperationalPeriod::KEY,
                'day'        => $day,
                'start_time' => $opWin['start'],
                'end_time'   => $opWin['end'],
            ];
        }

        return null;
    }
}
