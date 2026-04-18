<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ChangeDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class OperationalPeriodHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return OperationalPeriod::KEY;
    }

    public function isExclusive(): bool
    {
        return true;
    }
    public function allowedActions(): array
    {
        return ["keep", "modify"];
    }

    public function generate(Node $node, $blockers = []): array
    {
        return [
            "modify_self" => [
                 new SuggestionDTO(
                    "modify",
                    $node,
                    [
                         new ChangeDTO(
                            "end_time",
                            "modify",
                            "Operational Period Violated"
                         )
                    ]
                 )
            ]
        ];
    }
}
