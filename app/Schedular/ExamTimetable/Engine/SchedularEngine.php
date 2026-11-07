<?php

namespace App\Schedular\ExamTimetable\Engine;

use App\Schedular\ExamTimetable\Builders\ResponseBuilder\ResponseBuilder;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Registry\ConstraintRegistry;
use App\Schedular\ExamTimetable\Context\ExamTimetableContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;
use App\Schedular\ExamTimetable\Exceptions\HardConstraintFailureException;
use App\Schedular\ExamTimetable\Grid\DayBuilder;
use App\Schedular\ExamTimetable\Placement\Engine\PlacementEngine;

/**
 * Generates an exam timetable, in the same stages as the semester timetable:
 *
 *  1. DayBuilder          checks the hard constraints, builds the exam dates and places joint courses
 *  2. ConstraintRegistry  places courses requested at a fixed time (before placement)
 *  3. PlacementEngine     gives every other course a date, a time, halls and invigilators
 *  4. ConstraintRegistry  checks hall, invigilator and daily load requests (after placement)
 *  5. ResponseBuilder     status, timetable and diagnostics
 */
class SchedularEngine
{
    public function run(array $requestPayload): ResponseDTO
    {
        ExamTimetableContext::setRequestPayload($requestPayload);
        $state = new State();
        $constraints = app(ConstraintRegistry::class);

        try {
            app(DayBuilder::class)->build($state);
            $constraints->enforce($state, ConstraintHandler::BEFORE_PLACEMENT);
            app(PlacementEngine::class)->run($state);
            $constraints->enforce($state, ConstraintHandler::AFTER_PLACEMENT);
        } catch (HardConstraintFailureException $e) {
            // The hard violations are in the state; the response reports them.
        }

        return app(ResponseBuilder::class)->build($state);
    }
}
