<?php

namespace App\Constant\Constraint\SemesterTimetable\Soft;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;

class TeacherMaxDailyHourConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Max Daily Teaching Hours')
            ->programName('teacher_max_daily_hours')
            ->type('Soft')
            ->code('TDH')
            ->description('Sets the maximum number of teaching hours any teacher can be assigned on a single day. Applies to all teachers by default, with optional exceptions for specific teachers.')
            ->examples([
                [
                    "max_hours" => 6
                ],
                [
                    "max_hours" => 7
                ],
                [
                    "max_hours" => 5,
                    "teacher_exceptions" => [
                        [
                            "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                            "max_hours"  => 8
                        ]
                    ]
                ],
                [
                    "max_hours" => 6,
                    "teacher_exceptions" => [
                        [
                            "teacher_id" => "123e4567-e89b-12d3-a456-426614174000",
                            "max_hours"  => 4
                        ],
                        [
                            "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                            "max_hours"  => 7
                        ]
                    ]
                ],
                [
                    "max_hours" => 8
                ]
            ])
            ->build();
    }
}
