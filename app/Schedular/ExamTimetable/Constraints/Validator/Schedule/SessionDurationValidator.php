<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\SessionDuration;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Context\RequestContext;
use Carbon\Carbon;

class SessionDurationValidator implements ValidatorInterface
{
    public function check(array $params): ?array
    {
        $context = RequestContext::fromPayload();
        $courseId = $params['course_id'] ?? null;
        $start    = Carbon::parse($params['start_time']);
        $end      = Carbon::parse($params['end_time']);
        $actual   = (float) $start->diffInMinutes($end);

        if ($courseId) {
            $expected = (float) $context->sessionDurationCourse($courseId);
            if ($actual !== $expected) {
                return [
                    'key'               => SessionDuration::KEY,
                    'allowed_duration' => [
                        "course_id" => $courseId,
                        "duration" => $expected
                    ],
                    "conflict"   => array_filter([
                        "course_id"  => $params["course_id"] ?? null,
                        "start_time" => $params["start_time"] ?? null,
                        "end_time"   => $params["end_time"] ?? null,
                        "day"        => $params["day"] ?? null,
                        "slot_type"  => $params["slot_type"] ?? null,
                        "teacher_id" => $params["teacher_id"] ?? null,
                        "hall_id"    => $params["hall_id"] ?? null,
                    ])
                ];
            }
        }

        if (!$courseId) {
            $expected = (float) $context->sessionDuration();
            if ($actual !== $expected) {
                return [
                    'key'               => SessionDuration::KEY,
                    'allowed_duration' => $expected,
                    "conflict"   => array_filter([
                        "course_id"  => $params["course_id"] ?? null,
                        "start_time" => $params["start_time"] ?? null,
                        "end_time"   => $params["end_time"] ?? null,
                        "day"        => $params["day"] ?? null,
                        "slot_type"  => $params["slot_type"] ?? null,
                        "teacher_id" => $params["teacher_id"] ?? null,
                        "hall_id"    => $params["hall_id"] ?? null,
                    ])
                ];
            }
        }

        return [];
    }
}
