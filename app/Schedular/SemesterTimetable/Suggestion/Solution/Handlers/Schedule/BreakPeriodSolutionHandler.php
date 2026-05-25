<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Schedule;

use App\Schedular\SemesterTimetable\Suggestion\Solution\Contract\SolutionPath;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodBlocker;
use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodConstraint;
use App\Constant\Action\AppActions;
class BreakPeriodSolutionHandler implements SolutionPath
{
    public function supports(string $targetType): bool
    {
        return $targetType === BreakPeriodBlocker::KEY || $targetType === BreakPeriodConstraint::KEY;
    }
    public function getSolution(object $resolution, array $resolvedSteps = []): array
    {
        $options = [];

        foreach ($resolution->options['proposals'] as $proposal) {
            $options[] = [
                "type" => "dependency",
                'option_id' => $proposal["id"] ?? null,
                'action' => AppActions::MODIFY,
                'condition' => [],
                'params' => collect($proposal)->except('id')->toArray(),
            ];
        }
        return $options;
    }
}
