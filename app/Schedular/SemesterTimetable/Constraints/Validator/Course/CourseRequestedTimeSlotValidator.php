<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Course;

use App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use Carbon\Carbon;
class CourseRequestedTimeSlotValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): array
    {
        $day = strtolower((string) ($params['day'] ?? ''));
        $startRaw = $params['start_time'] ?? null;
        $endRaw   = $params['end_time'] ?? null;

        if ($day === '' || empty($startRaw) || empty($endRaw)) {
            return [];
        }

        $reqStart = Carbon::parse($startRaw);
        $reqEnd   = Carbon::parse($endRaw);

        if ($reqEnd->lessThanOrEqualTo($reqStart)) {
            return [];
        }

        $windows = $context->cRequestedWindowsFor($day);
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
                    'day'        => $day,
                    'course_id'  => $rts['course_id'] ?? null,
                    'start_time' => $rtsStart->format('H:i'),
                    'end_time'   => $rtsEnd->format('H:i'),
                ];
            }
        }

        return $blockers;
    }
}
