<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Hall;

use App\Constant\Constraint\SemesterTimetable\Hall\HallRequestedTimeWindow;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;

class HallRequestedTimeSlotRes implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === HallRequestedTimeWindow::KEY;
    }

    public function resolve($resolution, $params): array
    {
        throw new \Exception('Not implemented');
    }
}
