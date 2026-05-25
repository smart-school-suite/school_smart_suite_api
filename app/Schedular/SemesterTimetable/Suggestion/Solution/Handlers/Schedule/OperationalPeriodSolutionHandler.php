<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Schedule;

use App\Constant\Action\AppActions;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Contract\SolutionPath;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodBlocker;
use App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodConstraint;

class OperationalPeriodSolutionHandler implements SolutionPath
{
    public function supports(string $targetType): bool
    {
        return $targetType === OperationalPeriodBlocker::KEY || $targetType === OperationalPeriodConstraint::KEY;
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
