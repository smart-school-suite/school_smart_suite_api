<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;

class CourseRequestedTimeSlotRes implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type  === CourseRequestedSlot::KEY;
    }
    public function resolve($resolution, $params): array
    {
        throw new \Exception('Not implemented');
    }
}
