<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Assignment;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Constant\Violation\ExamTimetable\Assignment\RequestedAssignment as RequestedAssignmentViolation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\ExamTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;
use Override;

class RequestedAssignmentBlocker implements BlockerBuilder
{
    #[Override]
    public function supports(string $type): bool
    {
        return $type === RequestedAssignmentViolation::KEY;
    }

    #[Override]
    public function build(array $blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->id = app(GenerateId::class)->generateId(array_filter([
            "type" => RequestedAssignmentConstraint::KEY,
            'course_id'  => $blocker['course_id'] ?? null,
            'date'        => $blocker['date'] ?? null,
            'start_time' => $blocker['start_time'] ?? null,
            'end_time'   => $blocker['end_time'] ?? null,
            'invigilator_id' => $assignment['invigilator_id'] ?? null,
            'hall_id'    => $assignment['hall_id'] ?? null,
        ]));
        $violation->type =  RequestedAssignmentViolation::KEY;
        $violation->entity = array_filter([
            "type" => RequestedAssignmentConstraint::KEY,
            'course_id'  => $blocker['course_id'] ?? null,
            'date'        => $blocker['date'] ?? null,
            'start_time' => $blocker['start_time'] ?? null,
            'end_time'   => $blocker['end_time'] ?? null,
            'invigilator_id' => $assignment['invigilator_id'] ?? null,
            'hall_id'    => $assignment['hall_id'] ?? null,
        ]);
        $violation->conflict = array_filter([...$blocker['conflict']]);
        return $violation;
    }
}
