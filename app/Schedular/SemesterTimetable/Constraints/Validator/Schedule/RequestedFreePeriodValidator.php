<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use Carbon\Carbon;
class RequestedFreePeriodValidator implements ValidatorInterface
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

        foreach ($context->requestedFreePeriodsFor($day) as $rfp) {
            $rfpStartRaw = $rfp['start_time'] ?? null;
            $rfpEndRaw   = $rfp['end_time'] ?? null;

            if (empty($rfpStartRaw) || empty($rfpEndRaw)) {
                continue;
            }

            $rfpStart = Carbon::parse($rfpStartRaw);
            $rfpEnd   = Carbon::parse($rfpEndRaw);

            if ($rfpEnd->lessThanOrEqualTo($rfpStart)) {
                continue;
            }

            $overlaps = $reqStart->lt($rfpEnd) && $rfpStart->lt($reqEnd);

            if ($overlaps) {
                $blockers[] = [
                    'key'        => RequestedFreePeriod::KEY,
                    'day'        => $day,
                    'start_time' => $rfpStart->format('H:i'),
                    'end_time'   => $rfpEnd->format('H:i'),
                ];
            }
        }

        return $blockers;
    }
}
