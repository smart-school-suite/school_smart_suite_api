<?php

namespace App\Constant\Constraint\SemesterTimetable\Soft;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;

class TeacherMaxWeeklyHoursConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Max Weekly Teaching Hours')
            ->programName('teacher_max_weekly_hours')
            ->type('Soft')
            ->code('TWH')
            ->description('Sets the maximum total number of teaching hours any teacher can be assigned across the entire week. Applies to all teachers by default, with optional exceptions for specific teachers.')
            ->examples([
                [
                    "max_hours" => 30
                ],
                [
                    "max_hours" => 24
                ],
                [
                    "max_hours" => 28,
                    "teacher_exceptions" => [
                        [
                            "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                            "max_hours"  => 25
                        ]
                    ]
                ],
                [
                    "max_hours" => 32,
                    "teacher_exceptions" => [
                        [
                            "teacher_id" => "123e4567-e89b-12d3-a456-426614174000",
                            "max_hours"  => 35
                        ],
                        [
                            "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                            "max_hours"  => 28
                        ]
                    ]
                ],
                [
                    "max_hours" => 20
                ]
            ])
            ->build();
    }
}
