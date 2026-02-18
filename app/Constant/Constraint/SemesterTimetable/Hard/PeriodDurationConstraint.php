<?php

namespace App\Constant\Constraint\SemesterTimetable\Hard;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;
class PeriodDurationConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Period Duration')
            ->programName('schedule_period_duration_minutes')
            ->type('Hard')
            ->code('PDU')
            ->description('Sets the standard length (in minutes) of each class period or timetable slot. All periods must follow this duration every day unless specific day exceptions are defined.')
            ->examples([
                [
                    "duration_minutes" => 60
                ],
                [
                    "duration_minutes" => 45
                ],
                [
                    "duration_minutes" => 90,
                    "day_exceptions" => [
                        [
                            "day"              => "tuesday",
                            "duration_minutes" => 120
                        ]
                    ]
                ],
                [
                    "duration_minutes" => 45,
                    "day_exceptions" => [
                        [
                            "day"              => "monday",
                            "duration_minutes" => 60
                        ],
                        [
                            "day"              => "friday",
                            "duration_minutes" => 30
                        ]
                    ]
                ],
                [
                    "duration_minutes" => 50,
                    "day_exceptions" => [
                        [
                            "day"              => "thursday",
                            "duration_minutes" => 100
                        ]
                    ]
                ]
            ])
            ->build();
    }
}
