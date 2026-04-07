<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyFreePeriod;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;

class DailyFreePeriodValidator implements ValidatorInterface
{
    public const REQUESTEDFREEPERIOD = RequestedFreePeriod::KEY;

    public function check(ConstraintContext $context, array $params): array
    {
        $day       = strtolower($params['day']);
        $startTime = $params['start_time'];
        $endTime   = $params['end_time'];

        $dailyFreePeriod = $context->dailyFreePeriodsFor($day);

        if (empty($dailyFreePeriod)) {
            return [];
        }

        $maxFree = $dailyFreePeriod['max_free_periods'] ?? null;
        $minFree = $dailyFreePeriod['min_free_periods'] ?? null;

        if ($maxFree === null && $minFree === null) {
            return [];
        }

        // ── Seed with the incoming slot ───────────────────────────────────
        $freePeriods = [
            ['start_time' => $startTime, 'end_time' => $endTime],
        ];

        // ── Exclude the incoming slot from sources ────────────────────────
        $isNotIncoming = fn($slot) =>
            $slot['start_time']          !== $startTime ||
            $slot['end_time']            !== $endTime   ||
            strtolower($slot['day'])     !== $day;

        // Collect all other requested free periods for this day
        $context->requestedFreePeriodsFor($day)
            ->filter($isNotIncoming)
            ->each(fn($slot) => $freePeriods[] = [
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
            ]);

        // ── Count ─────────────────────────────────────────────────────────
        $totalFree = count($freePeriods);

        // ── Check bounds ──────────────────────────────────────────────────
        if ($maxFree !== null && $totalFree >= $maxFree) {
            return [
                'key'          => ScheduleDailyFreePeriod::KEY,
                'breach'       => 'over',
                'day'          => $day,
                'start_time'   => $startTime,
                'end_time'     => $endTime,
                'total_free'   => $totalFree,
                'max_free'     => $maxFree,
                'min_free'     => $minFree,
            ];
        }

        if ($minFree !== null && $totalFree < $minFree) {
            return [
                'key'          => ScheduleDailyFreePeriod::KEY,
                'breach'       => 'under',
                'day'          => $day,
                'start_time'   => $startTime,
                'end_time'     => $endTime,
                'total_free'   => $totalFree,
                'max_free'     => $maxFree,
                'min_free'     => $minFree,
            ];
        }

        return [];
    }
}
