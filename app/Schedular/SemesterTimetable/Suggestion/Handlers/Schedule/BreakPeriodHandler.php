<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodBlocker;
use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodConstraint;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Core\BlockerRegistry;

class BreakPeriodHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return $type === BreakPeriodBlocker::KEY || $type === BreakPeriodConstraint::KEY;
    }
    public function isExclusive(): bool
    {
        return true;
    }

    public function allowedActions(): array
    {
        return ["modify", "remove", "keep"];
    }

    public function conflictOptions($constraint): array
    {
        return [
            new SuggestionOptionDTO(
                action: 'modify',
                label: 'Move Break Period to another time',
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
