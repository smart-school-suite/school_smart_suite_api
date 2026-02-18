<?php

namespace App\Constant\Constraint\SemesterTimetable\Soft;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;

class RequestedFreePeriodSlotConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Preferred Free Periods')
            ->programName('requested_free_periods')
            ->type('Soft')
            ->code('RFP')
            ->description('Specifies preferred days and/or time windows where the user would like free periods (gaps or empty slots) to appear in the student timetable. These are soft preferences — the scheduler should try to create these free slots when possible, but may fill or adjust them if needed due to other constraints.')
            ->examples([
                [
                    [
                        "day"        => "monday",
                        "start_time" => "10:00",
                        "end_time"   => "11:00"
                    ],
                    [
                        "day"        => "friday",
                        "start_time" => "10:00",
                        "end_time"   => "11:00"
                    ]
                ],
                [
                    [
                        "day" => "monday"
                    ],
                    [
                        "day" => "friday"
                    ]
                ],
                [
                    [
                        "start_time" => "12:00",
                        "end_time"   => "13:00"
                    ]
                ],
                [
                    [
                        "day"        => "wednesday"
                    ],
                    [
                        "day"        => "thursday",
                        "start_time" => "11:30",
                        "end_time"   => "12:30"
                    ],
                    [
                        "start_time" => "14:00",
                        "end_time"   => "15:00"
                    ]
                ],
                [
                    [
                        "day" => "tuesday"
                    ]
                ]
            ])
            ->build();
    }
}
