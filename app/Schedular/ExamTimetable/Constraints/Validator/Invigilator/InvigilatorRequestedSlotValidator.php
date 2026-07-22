<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Context\RequestContext;
use Carbon\Carbon;

class InvigilatorRequestedSlotValidator implements ValidatorInterface
{
    public function check(array $params): ?array
    {
        $context = RequestContext::fromPayload();
        $date = $params['date'];
        $invigId = $params['invigilator_id'] ?? null;
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

        foreach ($context->invigRequestedSlotDate($date) as $trw) {
            $trwStartRaw = $trw['start_time'] ?? null;
            $trwEndRaw   = $trw['end_time'] ?? null;

            if (empty($trwStartRaw) || empty($trwEndRaw)) {
                continue;
            }

            $trwStart = Carbon::parse($trwStartRaw);
            $trwEnd   = Carbon::parse($trwEndRaw);

            if ($trwEnd->lessThanOrEqualTo($trwStart)) {
                continue;
            }

            $overlaps = $reqStart->lt($trwEnd) && $trwStart->lt($reqEnd);

            if ($overlaps) {
                $blockers[] = [
                    'key'        => InvigilatorRequestedSlot::KEY,
                    'invigilator_id' => $trw["invigilator_id"],
                    'date'        => $date,
                    'start_time' => $reqStart->format('H:i'),
                    'end_time'   => $reqEnd->format('H:i'),
                    'conflict' => array_filter([
                        "course_id" => $params["course_id"] ?? null,
                        "hall_id" => $params["hall_id"] ?? null,
                        "invigilator_id" => $params["invigilator_id"] ?? null,
                        "date" => $params["date"] ?? null,
                        "start_time" => $params["start_time"] ?? null,
                        "end_time" => $params["end_time"] ?? null,
                        "constraint_type" => $params["slot_type"] ?? null
                    ])
                ];
            }
        }

        return $blockers;
    }
}
