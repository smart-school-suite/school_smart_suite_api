<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\SessionDuration;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BuildsConflict;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Core\State;

class SessionDurationValidator implements ValidatorInterface
{
    use BuildsConflict;

    public function check(State $state, array $params): array
    {
        $courseId = $params['course_id'] ?? null;

        if (!$courseId) {
            return [];
        }

        $expected = $state->context->duration($courseId);
        $actual = $params['end'] - $params['start'];

        if ($actual === $expected) {
            return [];
        }

        return [[
            'key' => SessionDuration::KEY,
            'course_id' => $courseId,
            'duration' => $expected,
            'requested_duration' => $actual,
            'conflict' => $this->conflict($params),
        ]];
    }
}
