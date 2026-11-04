<?php

namespace App\Schedular\ExamTimetable\Engine;

use App\Schedular\ExamTimetable\Builders\ResponseBuilder\ResponseBuilder;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Registry\ConstraintRegistry;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;
use App\Schedular\ExamTimetable\Exceptions\HardConstraintFailureException;
use App\Schedular\ExamTimetable\Grid\DayBuilder;
use App\Schedular\ExamTimetable\Placement\PlacementEngine;

/**
 * Exam timetable scheduler.
 *
 * 1. DayBuilder        one grid entry per operational exam date, joint course sessions fixed in it (hard)
 * 2. Constraints       requested assignments and course time requests pinned (soft)
 * 3. PlacementEngine   every other course gets a date, time, halls and invigilators (hard when impossible)
 * 4. Constraints       invigilator/hall requests and the student daily load range reviewed (soft)
 * 5. ResponseBuilder   timetable, diagnostics and solution paths
 */
class SchedularEngine
{
    public function __construct(
        private DayBuilder $dayBuilder,
        private PlacementEngine $placementEngine,
        private ConstraintRegistry $constraints,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function run(array $requestPayload, bool $withSolutions = true): ResponseDTO
    {
        return $this->responseBuilder->build($this->schedule($requestPayload), $withSolutions);
    }

    /** Runs the scheduler and returns the raw state, without diagnostics or solutions. */
    public function schedule(array $requestPayload): State
    {
        $state = new State(RequestContext::fromPayload($requestPayload));

        try {
            $this->dayBuilder->build($state);
            $this->constraints->enforce($state, ConstraintHandler::BEFORE_PLACEMENT);
            $this->placementEngine->run($state);
            $this->constraints->enforce($state, ConstraintHandler::AFTER_PLACEMENT);
        } catch (HardConstraintFailureException) {
            // The violation that stopped generation is already recorded on the state.
        }

        return $state;
    }
}
