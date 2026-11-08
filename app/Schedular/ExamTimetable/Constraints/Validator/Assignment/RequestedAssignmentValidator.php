<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Assignment;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Constant\Violation\ExamTimetable\Assignment\RequestedAssignment;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BaseValidator;
use App\Schedular\ExamTimetable\Core\State;

/** Another course was already assigned to that time for the same students. */
class RequestedAssignmentValidator extends BaseValidator
{
    public function check(array $params, State $state): array
    {
        return array_map(fn(array $session) => [
            'key' => RequestedAssignment::KEY,
            'course_id' => $session['course_id'],
            'date' => $params['date'],
            'start_time' => $session['start_time'],
            'end_time' => $session['end_time'],
            'hall_ids' => array_column($session['halls'], 'hall_id'),
            'conflict' => $this->conflict($params),
        ], $this->overlappingSessions($params, $state, RequestedAssignmentConstraint::KEY));
    }
}
