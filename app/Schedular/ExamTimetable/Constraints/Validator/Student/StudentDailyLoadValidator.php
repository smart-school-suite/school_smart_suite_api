<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Student;

use App\Constant\Violation\ExamTimetable\Student\StudentDailyLoad;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BaseValidator;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;

/** One more exam on the date would go over the daily maximum. */
class StudentDailyLoadValidator extends BaseValidator
{
    public function check(array $params, State $state): array
    {
        $date = $params['date'] ?? null;
        if (!$date || !isset($state->dateGrid[$date])) {
            return [];
        }

        $max = RequestContext::fromPayload()->dailyLoadFor($date)['max_sessions'];
        $courseId = $params['course_id'] ?? null;
        $load = $state->load($date, $courseId);

        if ($max === null || $load + 1 <= $max) {
            return [];
        }

        return [[
            'key' => StudentDailyLoad::KEY,
            'date' => $date,
            'breach' => 'max',
            'max_sessions' => $max,
            'sessions' => $load,
            'course_ids' => array_column($state->sessions($date, $courseId), 'course_id'),
            'conflict' => $this->conflict($params),
        ]];
    }
}
