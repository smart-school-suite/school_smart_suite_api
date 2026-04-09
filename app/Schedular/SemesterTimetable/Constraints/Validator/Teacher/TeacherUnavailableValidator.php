<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Carbon\Carbon;

class TeacherUnavailableValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): array
    {
        $teacherId = $params['teacher_id'] ?? null;
        $day = strtolower($params['day']);
        $start = Carbon::parse($params['start_time']);
        $end = Carbon::parse($params['end_time']);

        if ($teacherId === null && isset($params['course_id'])) {
            $teacherId = $context->tCourses()
                ->firstWhere('course_id', $params['course_id'])['teacher_id'] ?? null;
        }

        if ($teacherId === null && !isset($params['course_id'])) {
            return $this->checkAllTeachersAvailability($context, $day, $start, $end, $params);
        }

        return $this->checkSpecificTeacherAvailability($context, $teacherId, $day, $start, $end, $params);
    }

    private function checkAllTeachersAvailability(
        ConstraintContext $context,
        string $day,
        Carbon $start,
        Carbon $end,
        array $params
    ): array {
        $unavailableTeachers = [];

        foreach ($context->teachers() as $teacher) {
            $teacherId = $teacher['teacher_id'] ?? $teacher['id'] ?? null;

            if ($teacherId === null) {
                continue;
            }

            $prefs = $context->tPreferredSlotsFor($teacherId, $day);

            if ($prefs->isEmpty()) {
                $unavailableTeachers[] = [
                    'key' => TeacherUnavailable::KEY,
                    'teacher_id' => $teacherId,
                    'day' => $day,
                    'start_time' => $params['start_time'],
                    'end_time' => $params['end_time'],
                    "conflict" => array_filter([
                        "course_id" => $params["course_id"] ?? null,
                        "hall_id" => $params["hall_id"] ?? null,
                        "slot_type" => $params["slot_type"] ?? null,
                        "teacher_id" => $params["teacher_id"] ?? null,
                        "day" => $params["day"] ?? null,
                        "start_time" => $params["start_time"] ?? null,
                        "end_time" => $params["end_time"] ?? null,
                    ])
                ];
                continue;
            }

            $isAvailable = false;
            foreach ($prefs as $pref) {
                if (strtolower($pref['day']) !== $day) {
                    continue;
                }

                $prefStart = Carbon::parse($pref['start_time']);
                $prefEnd = Carbon::parse($pref['end_time']);

                if ($start->greaterThanOrEqualTo($prefStart) && $end->lessThanOrEqualTo($prefEnd)) {
                    $isAvailable = true;
                    break;
                }
            }

            if (!$isAvailable) {
                $unavailableTeachers[] = [
                    'key' => TeacherUnavailable::KEY,
                    'teacher_id' => $teacherId,
                    'day' => $day,
                    'start_time' => $params['start_time'],
                    'end_time' => $params['end_time'],
                    "conflict" => array_filter([
                        "course_id" => $params["course_id"] ?? null,
                        "hall_id" => $params["hall_id"] ?? null,
                        "slot_type" => $params["slot_type"] ?? null,
                        "teacher_id" => $params["teacher_id"] ?? null,
                        "day" => $params["day"] ?? null,
                        "start_time" => $params["start_time"] ?? null,
                        "end_time" => $params["end_time"] ?? null,
                    ])
                ];
            }
        }

        if (count($unavailableTeachers) < $context->teachers()->count()) {
            return [];
        }


        return $unavailableTeachers;
    }

    private function checkSpecificTeacherAvailability(
        ConstraintContext $context,
        mixed $teacherId,
        string $day,
        Carbon $start,
        Carbon $end,
        array $params
    ): array {
        $prefs = $context->tPreferredSlotsFor($teacherId, $day);

        if ($prefs->isEmpty()) {
            return [
                'key' => TeacherUnavailable::KEY,
                'teacher_id' => $teacherId,
                'day' => $day,
                'start_time' => $params['start_time'],
                'end_time' => $params['end_time'],
                "conflict" => array_filter([
                    "course_id" => $params["course_id"] ?? null,
                    "hall_id" => $params["hall_id"] ?? null,
                    "slot_type" => $params["slot_type"] ?? null,
                    "teacher_id" => $params["teacher_id"] ?? null,
                    "day" => $params["day"] ?? null,
                    "start_time" => $params["start_time"] ?? null,
                    "end_time" => $params["end_time"] ?? null,
                ])
            ];
        }

        foreach ($prefs as $pref) {
            if (strtolower($pref['day']) !== $day) {
                continue;
            }

            $prefStart = Carbon::parse($pref['start_time']);
            $prefEnd = Carbon::parse($pref['end_time']);

            if ($start->greaterThanOrEqualTo($prefStart) && $end->lessThanOrEqualTo($prefEnd)) {
                return [];
            }
        }

        return [
            'key' => TeacherUnavailable::KEY,
            'teacher_id' => $teacherId,
            'day' => $day,
            'start_time' => $params['start_time'],
            'end_time' => $params['end_time'],
            "conflict" => array_filter([
                "course_id" => $params["course_id"] ?? null,
                "hall_id" => $params["hall_id"] ?? null,
                "slot_type" => $params["slot_type"] ?? null,
                "teacher_id" => $params["teacher_id"] ?? null,
                "day" => $params["day"] ?? null,
                "start_time" => $params["start_time"] ?? null,
                "end_time" => $params["end_time"] ?? null,
            ])
        ];
    }
}
