<?php

namespace App\Constant\Timetable\Constraints\Guides;

use App\Constant\Timetable\Constraints\Guides\Hard\BreakPeriodGuide;
use App\Constant\Timetable\Constraints\Guides\Hard\OperationalPeriodGuide;
use App\Constant\Timetable\Constraints\Guides\Hard\PeriodDurationGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\CourseMaxDailyFrequencyGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\MaxPeriodPerDayGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\CourseRequestedTimeSlotGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\HallRequestedTimeWindowGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\MaxFreePeriodPerDayGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\RequestedAssignmentGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\RequestedFreePeriodGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\TeacherMaxDailyHourGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\TeacherMaxWeeklyHourGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\TeacherRequestedTimeWindowGuide;
class ConstraintGuideRegistry
{
    public static function getSoftConstraintGuides(): array {
         return [
             BreakPeriodGuide::make(),
             OperationalPeriodGuide::make(),
             PeriodDurationGuide::make(),
         ];
    }

    public static function getHardConstraintGuides(): array {
         return [
                CourseMaxDailyFrequencyGuide::make(),
                MaxPeriodPerDayGuide::make(),
                CourseRequestedTimeSlotGuide::make(),
                HallRequestedTimeWindowGuide::make(),
                MaxFreePeriodPerDayGuide::make(),
                RequestedAssignmentGuide::make(),
                RequestedFreePeriodGuide::make(),
                TeacherMaxDailyHourGuide::make(),
                TeacherMaxWeeklyHourGuide::make(),
                TeacherRequestedTimeWindowGuide::make(),

         ];
    }
}
