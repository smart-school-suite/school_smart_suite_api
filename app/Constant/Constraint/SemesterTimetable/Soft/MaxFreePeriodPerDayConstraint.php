<?php

namespace App\Constant\Constraint\SemesterTimetable\Soft;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;
class MaxFreePeriodPerDayConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Max Free Periods per Day')
            ->programName('schedule_max_free_periods_per_day')
            ->type('Soft')
            ->code('MFP')
            ->description('Sets the maximum number of unscheduled (free/gap) periods allowed per day in the student timetable. Applies to all days by default, with optional exceptions for specific days.')
            ->examples([
                [
                    "max_free_periods" => 2
                ],
                [
                    "max_free_periods" => 1
                ],
                [
                    "max_free_periods" => 0
                ],
                [
                    "max_free_periods" => 1,
                    "day_exceptions" => [
                        [
                            "day"              => "monday",
                            "max_free_periods" => 3
                        ],
                        [
                            "day"              => "friday",
                            "max_free_periods" => 0
                        ]
                    ]
                ],
                [
                    "max_free_periods" => 3,
                    "day_exceptions" => [
                        [
                            "day"              => "wednesday",
                            "max_free_periods" => 1
                        ]
                    ]
                ]
            ])
            ->build();
    }
}
