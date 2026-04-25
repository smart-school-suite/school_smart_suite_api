<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodBlocker;
use App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodConstraint;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class OperationalPeriodHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return $type === OperationalPeriodBlocker::KEY || $type === OperationalPeriodConstraint::KEY;
    }

    public function isExclusive(): bool
    {
        return true;
    }
    public function allowedActions(): array
    {
        return ["keep", "modify"];
    }

    public function conflictOptions($constraint): array
    {
        return [
            new SuggestionOptionDTO(
                action: 'modify',
                label: 'Change Operational Period  to another time',
                meta: ['field' => 'time']
            )
        ];
    }

    public function dependencyOptions($constraint, array $blockers): array
    {
        return [];
    }
}
