<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Course;

use App\Constant\Violation\ExamTimetable\Course\CourseRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Context\RequestContext;
use Carbon\Carbon;

class CourseRequestedSlotValidator implements ValidatorInterface
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

        $windows = $context->courseRequestedSlotDate($date);
        if ($windows->isEmpty()) {
            return [];
        }

        $blockers = [];

        foreach ($windows as $rts) {
            $rtsStartRaw = $rts['start_time'] ?? null;
            $rtsEndRaw   = $rts['end_time'] ?? null;

            if (empty($rtsStartRaw) || empty($rtsEndRaw)) {
                continue;
            }

            $rtsStart = Carbon::parse($rtsStartRaw);
            $rtsEnd   = Carbon::parse($rtsEndRaw);

            if ($rtsEnd->lessThanOrEqualTo($rtsStart)) {
                continue;
            }

            $overlaps = $reqStart->lt($rtsEnd) && $rtsStart->lt($reqEnd);

            if ($overlaps) {
                $blockers[] = [
                    'key'        => CourseRequestedSlot::KEY,
                    'day'        => $date,
                    'course_id'  => $rts['course_id'] ?? null,
                    'start_time' => $rtsStart->format('H:i'),
                    'end_time'   => $rtsEnd->format('H:i'),
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
