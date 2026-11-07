<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Course;

use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Violation\ExamTimetable\Course\RequiredJointCourse;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BaseValidator;
use App\Schedular\ExamTimetable\Core\State;

/** The students already sit a joint course exam at that time. */
class RequiredJointCourseSlotValidator extends BaseValidator
{
    public function check(array $params, State $state): array
    {
        return array_map(fn(array $session) => [
            'key' => RequiredJointCourse::KEY,
            'course_id' => $session['course_id'],
            'date' => $params['date'],
            'start_time' => $session['start_time'],
            'end_time' => $session['end_time'],
            'conflict' => $this->conflict($params),
        ], $this->overlappingSessions($params, $state, RequiredJointCourseSession::KEY));
    }
}
