<?php

namespace App\Schedular\ExamTimetable\Solution\Support;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;

/**
 * The generate request and the scheduler payload use the same constraint keys and shapes; the payload only
 * splits them into hard_constraints and soft_constraints. This maps between the two so a solution step is
 * written once (against the request) and replayed on the payload to verify it.
 */
final class ExamPayload
{
    public const HARD = [OperationalPeriod::KEY, SessionDuration::KEY, RequiredJointCourseSession::KEY];

    public const SOFT = [
        RequestedAssignment::KEY,
        CourseTimeRequest::KEY,
        InvigilatorRequestedSlot::KEY,
        HallRequestedSlot::KEY,
        StudentDailyLoadRange::KEY,
    ];

    public static function toRequest(array $payload): array
    {
        return array_filter([
            ...array_intersect_key($payload['hard_constraints'] ?? [], array_flip(self::HARD)),
            ...array_intersect_key($payload['soft_constraints'] ?? [], array_flip(self::SOFT)),
        ], fn($value) => $value !== null);
    }

    public static function withRequest(array $payload, array $request): array
    {
        foreach (self::HARD as $key) {
            if (array_key_exists($key, $request)) {
                $payload['hard_constraints'][$key] = $request[$key];
            }
        }

        foreach (self::SOFT as $key) {
            if (array_key_exists($key, $request)) {
                $payload['soft_constraints'][$key] = $request[$key];
            } else {
                unset($payload['soft_constraints'][$key]);
            }
        }

        return $payload;
    }
}
