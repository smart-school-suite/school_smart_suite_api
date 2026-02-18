<?php

namespace App\Constant\Constraint\SemesterTimetable\Soft;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;
class TeacherRequestedTimeWindowConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Preferred Teacher Time Windows')
            ->programName('teacher_requested_time_windows')
            ->type('Soft')
            ->code('TTW')
            ->description('Specifies preferred days and/or time windows for scheduling classes taught by specific teachers. These are soft preferences — the scheduler should try to respect them when possible, but can assign teaching sessions outside these windows if needed due to other constraints.')
            ->examples([
                [
                    [
                        "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                        "windows" => [
                            [
                                "day"        => "monday",
                                "start_time" => "08:00",
                                "end_time"   => "12:00"
                            ],
                            [
                                "day"        => "wednesday",
                                "start_time" => "13:00",
                                "end_time"   => "17:00"
                            ]
                        ]
                    ]
                ],
                [
                    [
                        "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "windows" => [
                            ["day" => "monday"],
                            ["day" => "wednesday"]
                        ]
                    ]
                ],
                [
                    [
                        "teacher_id" => "14236b55-0428-4ae6-9dfb-cad1f91c7e66",
                        "windows" => [
                            ["day" => "tuesday"],
                            ["day" => "thursday"]
                        ]
                    ]
                ],
                [
                    [
                        "teacher_id" => "2e966c75-13ed-4e73-aac0-3c854d5eb35d",
                        "windows" => [
                            [
                                "start_time" => "08:00",
                                "end_time"   => "13:00"
                            ]
                        ]
                    ]
                ],
                [
                    [
                        "teacher_id" => "42413159-0500-4e84-8523-92470cfe2c13",
                        "windows" => [
                            ["day" => "friday"]
                        ]
                    ]
                ]
            ])
            ->build();
    }
}
