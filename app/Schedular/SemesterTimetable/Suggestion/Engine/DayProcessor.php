<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

class DayProcessor
{
    public function process($diagnostic, $day): array
    {
        $constraintMap = app(ConstraintMapBuilder::class)->build($diagnostic);
        $groupBuilder = app(ConflictGroupBuilder::class)->build($constraintMap);
        $scenarioBuilder = app(ScenarioBuilder::class)->build($groupBuilder);

        return [
            "constraint_map" => $constraintMap,
            "conflict_groups" => $groupBuilder,
            "scenarios" => $scenarioBuilder,
            "day" => $day
        ];
    }
}
