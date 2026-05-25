<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Assignment;

use App\Constant\Action\AppActions;
use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment as RequestedAssigmentBlocker;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use Illuminate\Support\Str;
class AssignmentHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return $type === RequestedAssignmentConstraint::KEY || $type === RequestedAssigmentBlocker::KEY;
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
                label: 'Remove assignment',
                meta: $metaData,
                proposals: [
                    "id" => Str::uuid()->toString(),
                    ...$metaData
                ]
            ),
            new SuggestionOptionDTO(
                action: AppActions::MODIFY,
                label: 'Move assignment to another time',
                meta: $metaData
            )
        ];
    }

    public function dependencyOptions(array $constraint, array $blockers): array
    {
        $resolveChanges = app(BlockerRegistry::class)->generateBlockerSuggestions($blockers);
        return $resolveChanges;
    }
}
