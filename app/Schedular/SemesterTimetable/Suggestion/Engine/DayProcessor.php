<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

class DayProcessor
{
    public function process(array $diagnostic, string $day): array
    {
        $result = [];

        // Process soft constraints if not empty
        if (!empty($diagnostic['soft'])) {
            $softConstraintMap = app(ConstraintMapBuilder::class)->build($diagnostic['soft']);
            $softGroupBuilder = app(ConflictGroupBuilder::class)->buildSoft($softConstraintMap);
            $softScenarioBuilder = app(ScenarioBuilder::class)->buildSoft($softGroupBuilder);

            // $result["soft_groups"] = $softGroupBuilder;
            $result[] = $softScenarioBuilder;
        }

        // Process hard constraints if not empty
        if (!empty($diagnostic['hard'])) {
            $hardConstraintMap = app(ConstraintMapBuilder::class)->build($diagnostic['hard']);
            $hardGroupBuilder = app(ConflictGroupBuilder::class)->buildHard($hardConstraintMap);
            $hardScenarioBuilder = app(ScenarioBuilder::class)->buildHard($hardGroupBuilder);

            // $result["hard_groups"] = $hardGroupBuilder;
            $result[] = $hardScenarioBuilder;
        }

        return array_filter($result);
    }
}
