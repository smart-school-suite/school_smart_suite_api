<?php

namespace App\Schedular\SemesterTimetable\Constraints\Handlers\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyPeriod as ScheduleDailyPeriodConstraint;
use App\Schedular\SemesterTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;

class ScheduleDailyPeriod implements ConstraintHandler
{
    public static function supports(): string
    {
        return ScheduleDailyPeriodConstraint::KEY;
    }

    public function handle(array $requestPayload, State $state): void
    {
        $context = ConstraintContext::fromPayload($requestPayload);
        $opDays = $context->opDays();
        $timetableGrid = collect($state->grid);

        foreach ($opDays as $opDay) {
            $dailyPeriod = $context->dailyPeriodsFor($opDay);
            $placementCount = $context->hRequestedWindowsFor($opDay)->count();
            +$context->cRequestedWindowsFor($opDay);
            +$context->tRequestedWindowsFor($opDay);
            +$context->jointCourses($opDay);
            $regularSlots = $timetableGrid->where("type", GridSlotDTO::TYPE_REGULAR);


        }
        //placement constraints
        $hRequestedSlot = $context->hRequestedWindows();
        $cRequestedSlot = $context->cRequestedWindows();
        $tRequestedSlot = $context->tRequestedWindows();
        $rJointCourseSlot = $context->allJointCourses();

        //period count constraint

        //count placement constraints, hall, teacher, course, jcourse this can be used for max,
        //count placement constraint, hall, teacher, course, you can consider the current grid also,

        //--- enforcement mode
        //min we enforce by

    }
}
