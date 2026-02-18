<?php

namespace App\Constant\Constraint\Builders\SemesterTimetable;

use App\Constant\Constraint\SemesterTimetable\Hard\BreakPeriodConstraint;
use App\Constant\Constraint\SemesterTimetable\Hard\OperationalPeriodConstraint;
use App\Constant\Constraint\SemesterTimetable\Hard\PeriodDurationConstraint;
use App\Constant\Constraint\SemesterTimetable\Soft\CourseMaxDailyFrequencyConstraint;
use App\Constant\Constraint\SemesterTimetable\Soft\CourseRequestedTimeSlotConstraint;
use App\Constant\Constraint\SemesterTimetable\Soft\HallRequestedSlotConstraint;
use App\Constant\Constraint\SemesterTimetable\Soft\MaxFreePeriodPerDayConstraint;
use App\Constant\Constraint\SemesterTimetable\Soft\MaxPeriodPerDayConstraint;
use App\Constant\Constraint\SemesterTimetable\Soft\RequestedAssignmentConstraint;
use App\Constant\Constraint\SemesterTimetable\Soft\RequestedFreePeriodSlotConstraint;
use App\Constant\Constraint\SemesterTimetable\Soft\TeacherMaxDailyHourConstraint;
use App\Constant\Constraint\SemesterTimetable\Soft\TeacherMaxWeeklyHoursConstraint;
use App\Constant\Constraint\SemesterTimetable\Soft\TeacherRequestedTimeWindowConstraint;

class ConstraintRegistry
{
    public static function getSoftConstraintGuides(): array
    {
        return [
            CourseMaxDailyFrequencyConstraint::make(),
            CourseRequestedTimeSlotConstraint::make(),
            HallRequestedSlotConstraint::make(),
            MaxFreePeriodPerDayConstraint::make(),
            MaxPeriodPerDayConstraint::make(),
            RequestedAssignmentConstraint::make(),
            RequestedFreePeriodSlotConstraint::make(),
            TeacherMaxDailyHourConstraint::make(),
            TeacherMaxWeeklyHoursConstraint::make(),
            TeacherRequestedTimeWindowConstraint::make()
        ];
    }

    public static function getHardConstraintGuides(): array
    {
        return [
            BreakPeriodConstraint::make(),
            OperationalPeriodConstraint::make(),
            PeriodDurationConstraint::make()
        ];
    }
}
