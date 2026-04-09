<?php

namespace App\Schedular\SemesterTimetable\Constraints\Handlers\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseDailyFrequency;
use App\Schedular\SemesterTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Core\State;

class CourseDailyFrequecy implements ConstraintHandler
{
    public static function supports(): string
    {
        return CourseDailyFrequency::KEY;
    }

    public function handle(array $requestPayload, State $state): void
    {
        $context = ConstraintContext::fromPayload($requestPayload);
        //we first validate to see if its max or under
        $cDailyFrequency = $context->cDailyFrequency();
    }
}
