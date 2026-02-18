<?php

namespace App\Constant\Constraint\SemesterTimetable\Soft;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;
class CourseRequestedTimeSlotConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Preferred Time Slots')
            ->programName('course_requested_time_slots')
            ->type('Soft')
            ->code('PTS')
            ->description('Specifies preferred days and/or time windows for scheduling particular courses. These are wishes the scheduler should try to respect when possible, but can override if needed due to other constraints.')
            ->examples([
                [
                    [
                        "course_id" => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "slots" => [
                            [
                                "day" => "monday",
                                "start_time" => "09:00",
                                "end_time" => "10:30"
                            ],
                            [
                                "day" => "wednesday",
                                "start_time" => "14:00",
                                "end_time" => "15:30"
                            ]
                        ]
                    ]
                ],
                [
                    [
                        "course_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "slots" => [
                            ["day" => "monday"],
                            ["day" => "wednesday"]
                        ]
                    ]
                ],
                [
                    [
                        "course_id" => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "slots" => [
                            [
                                "start_time" => "08:00",
                                "end_time"   => "10:30"
                            ]
                        ]
                    ]
                ],
                [
                    [
                        "course_id" => "math101",
                        "slots" => [
                            ["day" => "tuesday"],
                            ["day" => "thursday"]
                        ]
                    ],
                    [
                        "course_id" => "physics202",
                        "slots" => [
                            [
                                "start_time" => "09:00",
                                "end_time"   => "10:45"
                            ]
                        ]
                    ]
                ],
                [
                    [
                        "course_id" => "english301",
                        "slots" => [
                            ["day" => "friday"]
                        ]
                    ]
                ]
            ])
            ->build();
    }
}
