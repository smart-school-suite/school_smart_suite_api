<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule;

use App\Constant\Action\AppActions;
use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodBlocker;
use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodConstraint;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Core\BlockerRegistry;
use Illuminate\Support\Str;

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
    public function conflictOptions(array $constraint): array
    {
        $metaData = [...$constraint["details"], "type" => $constraint["type"] ];
        return [
            new SuggestionOptionDTO(
                action: AppActions::REMOVE,
                label: 'Remove Requested Free Period',
                meta: $metaData,
                proposals: [
                    "id" => Str::uuid()->toString(),
                    ...$metaData
                ]
            ),
            new SuggestionOptionDTO(
                action: AppActions::MODIFY,
                label: 'Move Requested Free Period to another time',
                meta:  $metaData
            )
        ];
    }

    public function dependencyOptions(array $constraint, array $blockers): array
    {
        $resolveChanges = app(BlockerRegistry::class)->generateBlockerSuggestions($blockers);
        return $resolveChanges;
    }
}
