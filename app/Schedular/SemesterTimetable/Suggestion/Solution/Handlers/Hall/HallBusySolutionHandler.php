<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Contract\SolutionPath;
use App\Constant\Action\AppActions;

class HallBusySolutionHandler implements SolutionPath
{
    public function supports(string $targetType): bool
    {
        return $targetType === HallBusy::KEY;
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
