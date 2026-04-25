<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationBlocker;
use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationConstraint;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
class PeriodDurationHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return $type === PeriodDurationBlocker::KEY || $type === PeriodDurationConstraint::KEY;
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
                label: 'Modify period duration',
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
