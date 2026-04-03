<?php

namespace App\Schedular\SemesterTimetable\Engine;

use App\Schedular\SemesterTimetable\Builders\ResponseBuilder;
use App\Schedular\SemesterTimetable\Constraints\Registry\ConstraintRegistry;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\Exceptions\HardConstraintFailureException;
use App\Schedular\SemesterTimetable\Grid\GridBuilder;
use App\Schedular\SemesterTimetable\Placement\PlacementEngine;

class SchedularEngine
{
    protected GridBuilder $gridBuilder;
    protected PlacementEngine $placementEngine;

    public function __construct(GridBuilder $gridBuilder, PlacementEngine $placementEngine)
    {
        $this->gridBuilder = $gridBuilder;
        $this->placementEngine = $placementEngine;
    }
    public function run(array $requestPayload)
    {
        $responseBuilder = app(ResponseBuilder::class);
        $constraintEnforcerEngine = app(ConstraintRegistry::class);
        $state = new State();
        try {
            $this->gridBuilder->buildGrid($state, $requestPayload);
            $this->placementEngine->place($state, $requestPayload);
            $constraintEnforcerEngine->enforceConstraints($requestPayload, $state);
            return $responseBuilder->build($state);
        } catch (HardConstraintFailureException $e) {
            return $responseBuilder->build($state);
        }
    }
}
