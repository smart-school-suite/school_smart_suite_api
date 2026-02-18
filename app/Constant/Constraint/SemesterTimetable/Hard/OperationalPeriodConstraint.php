<?php

namespace App\Constant\Constraint\SemesterTimetable\Hard;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;

class OperationalPeriodConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Operational Hours')
            ->programName('operational_period')
            ->type('Hard')
            ->code('OPR')
            ->description('Defines the daily opening-to-closing hours of the institution. No classes, exams, activities or any scheduling is allowed outside these hours on any day (unless exceptions are specified).')
            ->examples([
                [
                    "start_time" => "07:00",
                    "end_time"   => "18:00"
                ],
                [
                    "start_time" => "08:00",
                    "end_time"   => "17:00",
                    "day_exceptions" => [
                        [
                            "day"        => "wednesday",
                            "start_time" => "08:00",
                            "end_time"   => "16:00"
                        ]
                    ]
                ],
                [
                    "start_time" => "07:30",
                    "end_time"   => "16:30",
                    "day_exceptions" => [
                        [
                            "day"        => "friday",
                            "start_time" => "07:30",
                            "end_time"   => "14:00"
                        ],
                        [
                            "day"        => "saturday",
                            "start_time" => "08:00",
                            "end_time"   => "13:00"
                        ]
                    ]
                ],
                [
                    "start_time" => "08:00",
                    "end_time"   => "15:00",
                    "day_exceptions" => [
                        [
                            "day"        => "monday",
                            "start_time" => "07:30",
                            "end_time"   => "16:30"
                        ]
                    ]
                ],
                [
                    "start_time" => "07:00",
                    "end_time"   => "19:00",
                    "day_exceptions" => [
                        [
                            "day"        => "saturday",
                            "start_time" => "08:00",
                            "end_time"   => "14:00"
                        ]
                    ]
                ]
            ])
            ->build();
    }
}
