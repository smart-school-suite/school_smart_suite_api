<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\SessionDuration;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BaseValidator;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/** A requested course time must last exactly the course's exam duration. */
class SessionDurationValidator extends BaseValidator
{
    public function check(array $params, State $state): array
    {
        if (empty($params['course_id']) || empty($params['start_time']) || empty($params['end_time'])) {
            return [];
        }

        $expected = RequestContext::fromPayload()->sessionDurationCourse($params['course_id']);
        $actual = Time::duration($params['start_time'], $params['end_time']);

        if ($actual === $expected) {
            return [];
        }

        return [[
            'key' => SessionDuration::KEY,
            'course_id' => $params['course_id'],
            'allowed_duration' => $expected,
            'requested_duration' => $actual,
            'conflict' => $this->conflict($params),
        ]];
    }
}
