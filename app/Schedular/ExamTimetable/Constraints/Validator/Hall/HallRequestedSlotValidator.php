<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallRequestedSlot;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Context\RequestContext;
use Carbon\Carbon;

class HallRequestedSlotValidator implements ValidatorInterface
{
    public function check(array $params): ?array
    {
        $context = RequestContext::fromPayload();
        $date = $params['date'];
        $hallId = $params['hall_id'] ?? null;
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
                    'key'        => HallRequestedSlot::KEY,
                    'hall_id' => $trw["hall_id"],
                    'date'        => $date,
                    'start_time' => $reqStart->format('H:i'),
                    'end_time'   => $reqEnd->format('H:i'),
                ];
            }
        }

        return $blockers;
    }
}
