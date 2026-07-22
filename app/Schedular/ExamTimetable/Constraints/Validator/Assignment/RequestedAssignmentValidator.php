<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Assignment;

use App\Constant\Violation\ExamTimetable\Assignment\RequestedAssignment;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Context\RequestContext;
use Carbon\Carbon;
class RequestedAssignmentValidator implements ValidatorInterface
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

        foreach ($context->requestedAssignmentDate($date) as $assignment) {
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
                    'key'        => RequestedAssignment::KEY,
                    'course_id'  => $assignment['course_id'] ?? null,
                    'date'        => $assignment['date'] ?? $date,
                    'start_time' => $aStart->format('H:i'),
                    'end_time'   => $aEnd->format('H:i'),
                    'invigilator_id' => $assignment['invigilator_id'] ?? null,
                    'hall_id'    => $assignment['hall_id'] ?? null,
                    "conflict" => array_filter([
                        "course_id" => $params["course_id"] ?? null,
                        "hall_id" => $params["hall_id"] ?? null,
                        "constraint_type" => $params["slot_type"] ?? null,
                        "invigilator_id" => $params["invigilator_id"] ?? null,
                        "date" => $params["date"] ?? null,
                        "start_time" => $params["start_time"] ?? null,
                        "end_time" => $params["end_time"] ?? null,
                    ])
                ];
            }
        }

        return $blockers;
    }
}
