<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use Carbon\Carbon;

class HallBusyValidator implements ValidatorInterface
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
        foreach ($context->hBusySlotsFor($day) as $hbp) {
            $hbpStartRaw = $hbp['start_time'] ?? null;
            $hbpEndRaw   = $hbp['end_time'] ?? null;

            if (empty($hbpStartRaw) || empty($hbpEndRaw)) {
                continue;
            }

            $hbpStart = Carbon::parse($hbpStartRaw);
            $hbpEnd   = Carbon::parse($hbpEndRaw);

            if ($hbpEnd->lessThanOrEqualTo($hbpStart)) {
                continue;
            }

            $overlaps = $reqStart->lt($hbpEnd) && $hbpStart->lt($reqEnd);

            if ($overlaps) {
                $blockers[] = [
                    'key'        => HallBusy::KEY,
                    'hall_id'    => $hbp['hall_id'] ?? null,
                    'day'        => $hbp['day'] ?? $day,
                    'start_time' => $hbpStart->format('H:i'),
                    'end_time'   => $hbpEnd->format('H:i'),
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
