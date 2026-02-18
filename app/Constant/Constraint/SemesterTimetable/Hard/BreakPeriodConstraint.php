<?php

namespace App\Constant\Constraint\SemesterTimetable\Hard;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;

class BreakPeriodConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Break Period')
            ->programName('break_period')
            ->type('Hard')
            ->code('BRP')
            ->description('Fixed daily break or lunch time during which no classes, exams, sessions or activities can be scheduled. Applies every operational day unless exceptions are specified.')
            ->examples([
                [
                    "start_time" => "12:00",
                    "end_time"   => "13:00"
                ],
                [
                    "start_time" => "12:00",
                    "end_time"   => "13:00",
                    "no_break_exceptions" => ["monday", "wednesday"]
                ],
                [
                    "start_time"     => "12:00",
                    "end_time"       => "13:00",
                    "day_exceptions" => [
                        [
                            "day"        => "friday",
                            "start_time" => "14:00",
                            "end_time"   => "15:00"
                        ]
                    ]
                ],
                [
                    "start_time"          => "12:30",
                    "end_time"            => "13:30",
                    "no_break_exceptions" => ["saturday"],
                    "day_exceptions"      => [
                        [
                            "day"        => "friday",
                            "start_time" => "14:00",
                            "end_time"   => "14:45"
                        ]
                    ]
                ],
                [
                    "start_time" => "10:00",
                    "end_time"   => "10:30",
                    "no_break_exceptions" => ["sunday", "saturday"]
                ]
            ])
            ->build();
    }
}
