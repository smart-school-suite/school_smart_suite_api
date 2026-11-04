<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Assignment;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Schedular\ExamTimetable\Constraints\Handlers\Support\PinsRequestedSessions;
use App\Schedular\ExamTimetable\Core\State;

/** A course at a fixed date and time, optionally in a named hall with a named invigilator. */
class RequestedAssignment extends PinsRequestedSessions
{
    public static function supports(): string
    {
        return RequestedAssignmentConstraint::KEY;
    }

    protected function entries(State $state): array
    {
        return $state->context->requestedAssignments();
    }

    protected function identity(array $entry): array
    {
        return [
            'course_id' => $entry['course_id'] ?? null,
            'hall_id' => $entry['hall_id'] ?? null,
            'invigilator_id' => $entry['invigilator_id'] ?? null,
        ];
    }
}
