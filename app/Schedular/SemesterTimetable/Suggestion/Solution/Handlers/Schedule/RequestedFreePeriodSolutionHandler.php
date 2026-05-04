<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodBlocker;
use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodConstraint;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Contract\SolutionPath;
use App\Constant\Action\AppActions;

class RequestedFreePeriodSolutionHandler implements SolutionPath
{
    public function supports(string $targetType): bool
    {
        return $targetType === RequestedFreePeriodBlocker::KEY || $targetType === RequestedFreePeriodConstraint::KEY;
    }
    public function getSolution(object $resolution, array $resolvedSteps = []): array
    {
        $solutions = [];

        $resolvedParams = collect($resolvedSteps)
            ->where("action", AppActions::MODIFY)
            ->pluck("params")
            ->toArray();

        $removeOption = $this->getRemoveOption($resolution);
        $modificationOptions = $this->getModificationOptions($resolution);

        foreach ($modificationOptions as $option) {
            $hasConflict = false;

            foreach ($resolvedParams as $stepParams) {
                if ($this->hasConflict($option['params'], $stepParams)) {
                    $hasConflict = true;
                    break;
                }
            }

            if (!$hasConflict) {
                $solutions[] = $option;
            }
        }

        if ($removeOption !== null) {
            $solutions[] = $removeOption;
        }

        return $solutions;
    }
    protected function hasConflict(array $optionParams, array $stepParams): bool
    {
        $start1 = $optionParams['start_time'] ?? null;
        $end1 = $optionParams['end_time'] ?? null;

        $start2 = $stepParams['start_time'] ?? null;
        $end2 = $stepParams['end_time'] ?? null;

        if ($start1 === null || $end1 === null || $start2 === null || $end2 === null) {
            return false;
        }

        return $start1 < $end2 && $start2 < $end1;
    }
    protected function getModificationOptions(object $resolution): array
    {
        $options = [];
        $modificationOptions = collect($resolution->options)->where("action", AppActions::MODIFY)->first();

        if ($modificationOptions) {
            foreach ($modificationOptions->proposals as $proposal) {
                $options[] = [
                    'option_id' => $proposal["id"] ?? null,
                    'action' => $modificationOptions->action,
                    'condition' => [],
                    'params' => collect($proposal)->except('id')->toArray(),
                ];
            }
        }

        return $options;
    }
    protected function getRemoveOption(object $resolution): ?array
    {
        $removeOption = collect($resolution->options)->where("action", AppActions::REMOVE)->first();
        if ($removeOption) {
            return [
                'option_id' => $removeOption->proposals["id"] ?? null,
                'action' => $removeOption->action,
                'condition' => [],
                'params' => collect($removeOption->proposals)->except('id')->toArray(),
            ];
        }
        return null;
    }
}
