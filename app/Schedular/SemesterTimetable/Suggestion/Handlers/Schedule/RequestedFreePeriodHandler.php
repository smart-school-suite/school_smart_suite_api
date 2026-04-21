<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodBlocker;
use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodConstraint;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Core\BlockerRegistry;

class RequestedFreePeriodHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return $type === RequestedFreePeriodBlocker::KEY || $type === RequestedFreePeriodConstraint::KEY;
    }

    public function isExclusive(): bool
    {
        return false;
    }

    public function allowedActions(): array
    {
        return ["keep", "modify", "remove"];
    }
    public function conflictOptions($constraint): array
    {
        return [
            new SuggestionOptionDTO(
                action: 'remove',
                label: 'Remove Requested Free Period'
            ),
            new SuggestionOptionDTO(
                action: 'modify',
                label: 'Move Requested Free Period to another time',
                meta: ['field' => 'time']
            )
        ];
    }

    public function dependencyOptions($constraint, array $blockers): array
    {
        $resolveChanges = app(BlockerRegistry::class)->generateBlockerSuggestions($blockers);
        return $resolveChanges;
    }
}
