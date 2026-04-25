<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodBlocker;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use Carbon\Carbon;
class OperationalPeriodRes implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === OperationalPeriodConstraint::KEY || OperationalPeriodBlocker::KEY;
    }

    public function resolve($resolution, $params): array
    {
        $blocker = $resolution->meta["blocker"];
        $entity = $blocker->entity;
        $conflict = $blocker->conflict;

        $start = Carbon::parse($entity['start_time']);
        $end = Carbon::parse($entity['end_time']);

        $conflictStart = Carbon::parse($entity['start_time'])->setTimeFromTimeString($conflict['start_time']);
        $conflictEnd = Carbon::parse($entity['start_time'])->setTimeFromTimeString($conflict['end_time']);

        if ($conflictStart->lt($start)) {
            $start = $conflictStart;
        }

        if ($conflictEnd->gt($end)) {
            $end = $conflictEnd;
        }

        return [
            "type" => "operational_period",
            "start_time" => $start->format('H:i'),
            "end_time" => $end->format('H:i'),
            "day" => $entity['day']
        ];
    }
}
