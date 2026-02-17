<?php

namespace App\Constant\Timetable\Constraints\Guides\Soft;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;
class TeacherMaxWeeklyHourGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('teacher_max_weekly_hours')
            ->intent('Sets the maximum total number of teaching hours any teacher can be assigned across the entire week.')
            ->whenToUse('Use this constraint only when the user mentions a maximum weekly teaching limit, weekly workload cap, total lessons per week, or similar restriction for teachers.')
            ->requiredFields(['max_hours'])
            ->optionalFields(['teacher_exceptions'])
            ->howToUse([
                'max_hours is always required and applies as the default weekly limit to ALL teachers.',
                'Do NOT create the teacher_exceptions array unless the user explicitly names one or more teachers who should have a DIFFERENT (higher or lower) weekly limit than the default.',
                'Inside teacher_exceptions, every item must contain exactly two fields: teacher_id (required) and max_hours (required).',
                'Never invent, assume or add teacher_exceptions — only include them when the user clearly specifies exceptions for named teachers.',
                'The scheduler must strictly respect both the default max_hours and any exception values — never exceed the weekly total for any teacher.'
            ])
            ->examples([
                [
                    "max_hours" => 30,
                    "teacher_exceptions" => [
                        [
                            "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                            "max_hours" => 25
                        ],
                        [
                            "teacher_id" => "123e4567-e89b-12d3-a456-426614174000",
                            "max_hours" => 35
                        ]
                    ]
                ],
            ])
            ->build();
    }
}
