<?php

namespace App\Schedular\ExamTimetable\Engine;

use App\Schedular\ExamTimetable\Context\ExamTimetableContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Exceptions\HardConstraintFailureException;
use App\Schedular\ExamTimetable\Grid\DayBuilder;

class SchedularEngine
{
    public function run(array $requestPayload)
    {
        ExamTimetableContext::setRequestPayload($requestPayload);
        $state = new State();
        try {
             app(DayBuilder::class)->build($state);
            return [
                "grid" => $state->dateGrid
            ];
        } catch (HardConstraintFailureException $e) {
            throw $e;
        }
    }
}
