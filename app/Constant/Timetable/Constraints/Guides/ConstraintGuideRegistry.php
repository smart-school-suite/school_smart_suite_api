<?php

namespace App\Constant\Timetable\Constraints\Guides;

use App\Constant\Timetable\Constraints\Guides\Hard\BreakPeriodGuide;
use App\Constant\Timetable\Constraints\Guides\Hard\OperationalPeriodGuide;
use App\Constant\Timetable\Constraints\Guides\Hard\PeriodDurationGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\CourseMaxDailyFrequencyGuide;
use App\Constant\Timetable\Constraints\Guides\Soft\MaxPeriodPerDayGuide;
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
                MaxPeriodPerDayGuide::make()
         ];
    }
}
