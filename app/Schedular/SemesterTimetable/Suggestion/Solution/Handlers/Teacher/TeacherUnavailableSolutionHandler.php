<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Contract\SolutionPath;
use App\Constant\Action\AppActions;

class TeacherUnavailableSolutionHandler implements SolutionPath
{
    public function supports(string $targetType): bool
    {
        return $targetType === TeacherUnavailable::KEY;
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
