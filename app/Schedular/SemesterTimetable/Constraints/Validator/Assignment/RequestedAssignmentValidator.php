<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Assignment;

use App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use Carbon\Carbon;

class RequestedAssignmentValidator implements ValidatorInterface
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

        foreach ($context->requestedAssignmentsFor($day) as $assignment) {
            $aStartRaw = $assignment['start_time'] ?? null;
            $aEndRaw   = $assignment['end_time'] ?? null;

            if (empty($aStartRaw) || empty($aEndRaw)) {
                continue;
            }

            $aStart = Carbon::parse($aStartRaw);
            $aEnd   = Carbon::parse($aEndRaw);

            if ($aEnd->lessThanOrEqualTo($aStart)) {
                continue;
            }

            $overlaps = $reqStart->lt($aEnd) && $aStart->lt($reqEnd);

            if ($overlaps) {
                $blockers[] = [
                    'key'        => RequestedAssigment::KEY,
                    'course_id'  => $assignment['course_id'] ?? null,
                    'day'        => $assignment['day'] ?? $day,
                    'start_time' => $aStart->format('H:i'),
                    'end_time'   => $aEnd->format('H:i'),
                    'teacher_id' => $assignment['teacher_id'] ?? null,
                    'hall_id'    => $assignment['hall_id'] ?? null,
                ];
            }
        }

        return $blockers;
    }
}
