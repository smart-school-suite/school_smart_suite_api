<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Carbon\Carbon;

class TeacherBusyValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): array
    {
        $day = strtolower($params['day']);
        $teacherId = $params['teacher_id'] ?? null;
        $start = Carbon::parse($params['start_time']);
        $end = Carbon::parse($params['end_time']);

        if ($teacherId === null && isset($params['course_id'])) {
            $teacherId = $context->tCourses()
                ->firstWhere('course_id', $params['course_id'])['teacher_id'] ?? null;
        }

        if ($teacherId === null && !isset($params['course_id'])) {
            return $this->checkAllTeachersForAvailability($context, $day, $start, $end, $params);
        }

        return $this->checkSpecificTeacher($context, $teacherId, $day, $start, $end, $params);
    }

    private function checkAllTeachersForAvailability(
        ConstraintContext $context,
        string $day,
        Carbon $start,
        Carbon $end,
        array $params
    ): array {
        $teachersWithConflicts = [];
        $uniqueTeacherIds = [];

        foreach ($context->teachers() as $teacher) {
            $teacherId = $teacher['teacher_id'] ?? $teacher['id'] ?? null;

            if ($teacherId === null) {
                continue;
            }

            $uniqueTeacherIds[$teacherId] = true;

            foreach ($context->tBusySlotsFor($teacherId, $day) as $tbw) {
                $tbwStart = Carbon::parse($tbw['start_time']);
                $tbwEnd = Carbon::parse($tbw['end_time']);

                $overlaps = $start->lessThan($tbwEnd) && $end->greaterThan($tbwStart);

                if ($overlaps) {
                    $teachersWithConflicts[$teacherId] = [
                        'key' => TeacherBusy::KEY,
                        'day' => $day,
                        'teacher_id' => $tbw['teacher_id'] ?? null,
                        'start_time' => $tbw['start_time'],
                        'end_time' => $tbw['end_time'],
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
                    break;
                }
            }
        }

        foreach (array_keys($uniqueTeacherIds) as $teacherId) {
            if (!isset($teachersWithConflicts[$teacherId])) {
                return [];
            }
        }

        return array_values($teachersWithConflicts);
    }

    private function checkSpecificTeacher(
        ConstraintContext $context,
        mixed $teacherId,
        string $day,
        Carbon $start,
        Carbon $end,
        array $params
    ): array {
        $blockers = [];

        foreach ($context->tBusySlotsFor($teacherId, $day) as $tbw) {
            $tbwStart = Carbon::parse($tbw['start_time']);
            $tbwEnd = Carbon::parse($tbw['end_time']);

            $overlaps = $start->lessThan($tbwEnd) && $end->greaterThan($tbwStart);

            if ($overlaps) {
                $blockers[] = [
                    'key' => TeacherBusy::KEY,
                    'day' => $day,
                    'teacher_id' => $tbw['teacher_id'] ?? null,
                    'start_time' => $tbw['start_time'],
                    'end_time' => $tbw['end_time'],
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

        return $blockers;
    }
}
