<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;

class DailyFreePeriodValidator implements ValidatorInterface
{
    public const REQUESTEDFREEPERIOD = RequestedFreePeriod::KEY;
    public function check(ConstraintContext $context, array $params): array
    {
        $startTime = $params["start_time"];
        $endTime = $params["end_time"];
        $day = $params["day"];

        $dailyFreePeriod = $context->dailyFreePeriodsFor($day);
        $requestedFreePeriod = $context->requestedFreePeriodsFor($day);
        return [];
    }
}
