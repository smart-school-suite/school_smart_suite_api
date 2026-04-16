<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Assignment;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
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

    public function generate(Node $node): array
    {
        return [
            [
                'action' => 'remove',
                'target' => $node,
                'label' => 'Remove assignment'
            ],
            [
                'action' => 'modify',
                'target' => $node,
                'label' => 'Move assignment',
                'payload' => []
            ]
        ];
    }

    private function handleModification($node)
    {
        //we are modifying an existing assignment, so we need to find potential slots for this assignment
        $day = $node->meta->entity->day;
        $teacherId = $node->meta->entity->teacher_id;
        $hallId = $node->meta->entity->hall_id;


        $context = ConstraintContext::fromPayload(self::$requestPayload);
        //get potential slots for suggestion
        $slots = collect(self::$timetableGrid)->filter(fn($slot) => (strtolower($slot->day) == strtolower($day) &&
            (strtolower($slot->type) === GridSlotDTO::TYPE_REGULAR)));


    }
}
