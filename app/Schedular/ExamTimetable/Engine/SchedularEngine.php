<?php

namespace App\Schedular\ExamTimetable\Engine;

use App\Schedular\ExamTimetable\Builders\ResponseBuilder\ResponseBuilder;
use App\Schedular\ExamTimetable\Context\ExamTimetableContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Exceptions\HardConstraintFailureException;
use App\Schedular\ExamTimetable\Grid\DayBuilder;
use App\Schedular\ExamTimetable\Placement\Engine\PlacementEngine;

class SchedularEngine
{
    public function run(array $requestPayload)
    {
        ExamTimetableContext::setRequestPayload($requestPayload);
        $state = new State();
        try {
            app(DayBuilder::class)->build($state);
            app(PlacementEngine::class)->run($state);
            return app(ResponseBuilder::class)->build($state);
        } catch (HardConstraintFailureException $e) {
            return $state->violations;
            throw $e;
        }
    }
}
