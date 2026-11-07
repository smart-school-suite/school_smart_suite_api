<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Course;

use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Violation\ExamTimetable\Course\CourseRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BaseValidator;
use App\Schedular\ExamTimetable\Core\State;

/** Another course was already requested at that time for the same students. */
class CourseRequestedSlotValidator extends BaseValidator
{
    public function check(array $params, State $state): array
    {
        return array_map(fn(array $session) => [
            'key' => CourseRequestedSlot::KEY,
            'course_id' => $session['course_id'],
            'date' => $params['date'],
            'start_time' => $session['start_time'],
            'end_time' => $session['end_time'],
            'conflict' => $this->conflict($params),
        ], $this->overlappingSessions($params, $state, CourseTimeRequest::KEY));
    }
}
