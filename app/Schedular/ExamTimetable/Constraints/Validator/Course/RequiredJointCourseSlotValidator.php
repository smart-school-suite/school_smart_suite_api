<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Course;

use App\Constant\Violation\ExamTimetable\Course\RequiredJointCourse;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Context\RequestContext;
use Carbon\Carbon;

class RequiredJointCourseSlotValidator implements ValidatorInterface
{
    public function check(array $params): ?array
    {
        $context = RequestContext::fromPayload();
        $date = $params['date'];
        $startRaw = $params['start_time'] ?? null;
        $endRaw   = $params['end_time'] ?? null;

        if ($date === '' || empty($startRaw) || empty($endRaw)) {
            return [];
        }

        $reqStart = Carbon::parse($startRaw);
        $reqEnd   = Carbon::parse($endRaw);

        if ($reqEnd->lessThanOrEqualTo($reqStart)) {
            return [];
        }

        $blockers = [];

        foreach ($context->jointCourseDate($date) as $jc) {
            $jcStartRaw = $jc['start_time'] ?? null;
            $jcEndRaw   = $jc['end_time'] ?? null;

            if (empty($jcStartRaw) || empty($jcEndRaw)) {
                continue;
            }

            $jcStart = Carbon::parse($jcStartRaw);
            $jcEnd   = Carbon::parse($jcEndRaw);

            if ($jcEnd->lessThanOrEqualTo($jcStart)) {
                continue;
            }

            $overlaps = $reqStart->lt($jcEnd) && $jcStart->lt($reqEnd);

            if ($overlaps) {
                $blockers[] = [
                    'key'        => RequiredJointCourse::KEY,
                    'date'        => $date,
                    'course_id'  => $jc['course_id']  ?? null,
                    'teacher_id' => $jc['teacher_id'] ?? null,
                    'hall_id'    => $jc['hall_id']    ?? null,
                    'start_time' => $jcStart->format('H:i'),
                    'end_time'   => $jcEnd->format('H:i'),
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
