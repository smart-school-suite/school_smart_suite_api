<?php

namespace App\Constant\Constraint\SemesterTimetable\Soft;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;

class HallRequestedSlotConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Preferred Hall Time Windows')
            ->programName('hall_requested_time_windows')
            ->type('Soft')
            ->code('PHW')
            ->description('Specifies preferred days and/or time windows for scheduling activities in particular halls or rooms. These are soft preferences — the scheduler should try to follow them when possible, but can place activities elsewhere if needed.')
            ->examples([
                [
                    [
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "windows" => [
                            [
                                "day" => "monday",
                                "start_time" => "08:00",
                                "end_time" => "18:00"
                            ],
                            [
                                "day" => "tuesday",
                                "start_time" => "08:00",
                                "end_time" => "18:00"
                            ]
                        ]
                    ]
                ],
                [
                    [
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "windows" => [
                            ["day" => "monday"],
                            ["day" => "tuesday"]
                        ]
                    ]
                ],
                [
                    [
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "windows" => [
                            [
                                "start_time" => "08:00",
                                "end_time"   => "18:00"
                            ]
                        ]
                    ]
                ],
                [
                    [
                        "hall_id" => "288edee8-4f75-4eee-92b2-640c0fad4967",
                        "windows" => [
                            ["day" => "wednesday"],
                            ["day" => "thursday"]
                        ]
                    ],
                    [
                        "hall_id" => "474a559e-9387-4df3-957b-eb4e6088f913",
                        "windows" => [
                            [
                                "start_time" => "14:00",
                                "end_time"   => "17:00"
                            ]
                        ]
                    ]
                ],
                [
                    [
                        "hall_id" => "4d310f64-d4d4-47a4-93f7-08a13dda785f",
                        "windows" => [
                            ["day" => "friday"]
                        ]
                    ]
                ]
            ])
            ->build();
    }
}
