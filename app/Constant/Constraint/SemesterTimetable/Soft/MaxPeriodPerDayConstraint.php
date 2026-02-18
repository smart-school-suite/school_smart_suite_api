<?php

namespace App\Constant\Constraint\SemesterTimetable\Soft;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;

class MaxPeriodPerDayConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Max Periods per Day')
            ->programName('schedule_max_periods_per_day')
            ->type('Hard')
            ->code('MPD')
            ->description('Sets the maximum number of class periods or sessions that can be scheduled on any single day across the entire timetable. Applies to all days by default, with optional exceptions for specific days.')
            ->examples([
                [
                    "max_periods" => 6
                ],
                [
                    "max_periods" => 7
                ],
                [
                    "max_periods" => 5,
                    "day_exceptions" => [
                        [
                            "day"         => "monday",
                            "max_periods" => 7
                        ],
                        [
                            "day"         => "friday",
                            "max_periods" => 4
                        ]
                    ]
                ],
                [
                    "max_periods" => 8,
                    "day_exceptions" => [
                        [
                            "day"         => "saturday",
                            "max_periods" => 4
                        ]
                    ]
                ],
                [
                    "max_periods" => 4
                ]
            ])
            ->build();
    }
}
