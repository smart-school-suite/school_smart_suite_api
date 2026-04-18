<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Assignment;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ChangeDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class AssignmentHandler extends SuggestionContext implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return $type === RequestedAssignment::KEY;
    }

    public function isExclusive(): bool
    {
        return false;
    }

    public function allowedActions(): array
    {
        return ["keep", "modify", "remove"];
    }

    public function generate(Node $node, array $blockers = []): array
    {
        $resolveChanges = [];

        foreach ($blockers as $blocker) {

            switch ($blocker->type) {

                case 'teacher_busy':
                    $resolveChanges[] = new ChangeDTO(
                        field: 'teacher_id',
                        type: 'replace',
                        reason: 'teacher_busy'
                    );
                    break;

                case 'hall_busy':
                    $resolveChanges[] = new ChangeDTO(
                        field: 'hall_id',
                        type: 'replace',
                        reason: 'hall_busy'
                    );
                    break;

                case 'operational_period':
                    $resolveChanges[] = new ChangeDTO(
                        field: 'time',
                        type: 'shift',
                        reason: 'outside_operational_hours'
                    );
                    break;
            }
        }

        $resolve = [];
        if (!empty($resolveChanges)) {
            $resolve[] = new SuggestionDTO(
                action: 'modify',
                target: $node,
                changes: $resolveChanges,
                label: 'Adjust assignment to resolve conflicts'
            );
        }

        // 🔵 Self modification (independent)
        $modify = [
            new SuggestionDTO(
                action: 'modify',
                target: $node,
                changes: [
                    new ChangeDTO(
                        field: 'time',
                        type: 'shift',
                        reason: 'manual_adjustment'
                    )
                ],
                label: 'Move assignment to another time'
            ),
            new SuggestionDTO(
                action: 'remove',
                target: $node,
                label: 'Remove assignment'
            )
        ];

        return [
            'resolve_blockers' => $resolve,
            'modify_self' => $modify
        ];
    }

    protected function  suggestTeacher(): array {
        return [];
    }

    protected function suggestHall(): array {
        return [];
    }
}
