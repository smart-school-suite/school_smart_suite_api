<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Assignment;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment as RequestedAssigmentBlocker;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

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

    public function conflictOptions($constraint): array
    {
        return [
            new SuggestionOptionDTO(
                action: 'remove',
                label: 'Remove assignment'
            ),
            new SuggestionOptionDTO(
                action: 'modify',
                label: 'Move assignment to another time',
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
