<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

use App\Schedular\SemesterTimetable\Suggestion\Solution\Core\SolutionPathEngine;

class DayProcessor
{
    public function process(array $diagnostic): array
    {
        $result = [];

        if (!empty($diagnostic['soft'])) {
            $softConstraintMap = app(ConstraintMapBuilder::class)->build($diagnostic['soft']);
            $softGroupBuilder = app(ConflictGroupBuilder::class)->buildSoft($softConstraintMap);
            $softScenarioBuilder = app(ScenarioBuilder::class)->buildSoft($softGroupBuilder);

            $result[] = $softScenarioBuilder;
        }

        if (!empty($diagnostic['hard'])) {
            $hardConstraintMap = app(ConstraintMapBuilder::class)->build($diagnostic['hard']);
            $hardGroupBuilder = app(ConflictGroupBuilder::class)->buildHard($hardConstraintMap);
            $hardScenarioBuilder = app(ConflictGroupBuilder::class)->buildHard($hardGroupBuilder);

            $result[] = $hardScenarioBuilder;
        }
        $scenarios = array_merge(...$result);
        $solutionPaths = app(SolutionPathEngine::class)->generateSolutionPaths($scenarios);
        return $solutionPaths;
    }
}
