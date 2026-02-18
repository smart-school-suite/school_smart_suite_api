<?php

namespace App\Constant\Constraint\SemesterTimetable\Soft;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;
class CourseMaxDailyFrequencyConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Max Daily Course Frequency')
            ->programName('course_max_daily_frequency')
            ->type('Soft')
            ->code('MDF')
            ->description('Limits how many times the same course or subject can be scheduled on any single day. Applies to all courses by default, with optional exceptions for specific courses.')
            ->examples([
                [
                    "max_frequency" => 2
                ],
                [
                    "max_frequency" => 1
                ],
                [
                    "max_frequency" => 3
                ],
                [
                    "max_frequency" => 1,
                    "course_exceptions" => [
                        [
                            "course_id"     => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                            "max_frequency" => 3
                        ],
                        [
                            "course_id"     => "abcdef12-3456-7890-abcd-ef1234567890",
                            "max_frequency" => 2
                        ]
                    ]
                ],
                [
                    "max_frequency" => 2,
                    "course_exceptions" => [
                        [
                            "course_id"     => "123e4567-e89b-12d3-a456-426614174000",
                            "max_frequency" => 4
                        ]
                    ]
                ]
            ])
            ->build();
    }
}
