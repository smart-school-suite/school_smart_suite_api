<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use Carbon\Carbon;
class HallRequestedTimeSlotValidator implements ValidatorInterface
{
    public function check(ConstraintContext $context, array $params): ?array
    {
        $day = strtolower((string) ($params['day'] ?? ''));
        $hallId = $params['hall_id'] ?? null;
        $startRaw = $params['start_time'] ?? null;
        $endRaw   = $params['end_time'] ?? null;

        if ($day === '' || empty($startRaw) || empty($endRaw)) {
            return null;
        }

        $reqStart = Carbon::parse($startRaw);
        $reqEnd   = Carbon::parse($endRaw);

        if ($reqEnd->lessThanOrEqualTo($reqStart)) {
            return null;
        }

        foreach ($context->hRequestedWindowsFor($day) as $hrw) {
            $hrwStartRaw = $hrw['start_time'] ?? null;
            $hrwEndRaw   = $hrw['end_time'] ?? null;

            if (empty($hrwStartRaw) || empty($hrwEndRaw)) {
                continue;
            }

            $hrwStart = Carbon::parse($hrwStartRaw);
            $hrwEnd   = Carbon::parse($hrwEndRaw);

            if ($hrwEnd->lessThanOrEqualTo($hrwStart)) {
                continue;
            }

            $overlaps = $reqStart->lt($hrwEnd) && $hrwStart->lt($reqEnd);

            if ($overlaps) {
                return [
                    'key'        => HallBusy::KEY,
                    'hall_id'    => $hallId,
                    'day'        => $day,
                    'start_time' => $hrwStart->format('H:i'),
                    'end_time'   => $hrwEnd->format('H:i'),
                ];
            }
        }

        return null;
    }
}
