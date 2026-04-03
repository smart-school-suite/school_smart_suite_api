<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Course;

use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use Carbon\Carbon;
class JointCoursePeriodValidator implements ValidatorInterface
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

        $blockers = [];

        foreach ($context->jointCourses($day) as $jc) {
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
                    'day'        => $day,
                    'course_id'  => $jc['course_id']  ?? null,
                    'teacher_id' => $jc['teacher_id'] ?? null,
                    'hall_id'    => $jc['hall_id']    ?? null,
                    'start_time' => $jcStart->format('H:i'),
                    'end_time'   => $jcEnd->format('H:i'),
                ];
            }
        }

        return $blockers;
    }
}
