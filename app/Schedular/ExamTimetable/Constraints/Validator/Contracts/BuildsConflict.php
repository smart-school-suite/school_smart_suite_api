<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Contracts;

use App\Schedular\ExamTimetable\Support\Time;

trait BuildsConflict
{
    protected function conflict(array $params): array
    {
        return array_filter([
            'slot_type' => $params['slot_type'] ?? null,
            'course_id' => $params['course_id'] ?? null,
            'hall_id' => $params['hall_id'] ?? null,
            'invigilator_id' => $params['invigilator_id'] ?? null,
            'date' => $params['date'] ?? null,
            'start_time' => isset($params['start']) ? Time::toTime($params['start']) : null,
            'end_time' => isset($params['end']) ? Time::toTime($params['end']) : null,
        ], fn($value) => $value !== null);
    }
}
