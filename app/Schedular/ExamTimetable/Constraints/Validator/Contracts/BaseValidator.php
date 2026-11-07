<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Contracts;

use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Placement\Support\Time;

abstract class BaseValidator implements ValidatorInterface
{
    /** The request a blocker is in the way of. */
    protected function conflict(array $params): array
    {
        return array_filter([
            'course_id' => $params['course_id'] ?? null,
            'hall_id' => $params['hall_id'] ?? null,
            'invigilator_id' => $params['invigilator_id'] ?? null,
            'date' => $params['date'] ?? null,
            'start_time' => $params['start_time'] ?? null,
            'end_time' => $params['end_time'] ?? null,
            'slot_type' => $params['slot_type'] ?? null,
        ]);
    }

    protected function hasWindow(array $params): bool
    {
        return !empty($params['date']) && !empty($params['start_time']) && !empty($params['end_time'])
            && Time::toMinutes($params['end_time']) > Time::toMinutes($params['start_time']);
    }

    /** Sessions already placed that overlap the request and came from $source. */
    protected function overlappingSessions(array $params, State $state, string $source): array
    {
        if (!$this->hasWindow($params) || !isset($state->dateGrid[$params['date']])) {
            return [];
        }

        return array_values(array_filter(
            $state->sessionsOverlapping($params['date'], Time::toMinutes($params['start_time']), Time::toMinutes($params['end_time'])),
            fn(array $session) => $session['source'] === $source
        ));
    }
}
