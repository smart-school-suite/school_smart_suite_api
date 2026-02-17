<?php

namespace App\Constant\Timetable\Constraints\Guides\Soft;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;

class CourseMaxDailyFrequencyGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('course_max_daily_frequency')
            ->intent('Limits how many times the same course/subject can be scheduled on any single day.')
            ->whenToUse('Use this constraint only when the user mentions a maximum number of times a subject/course can appear per day, limit on daily repeats of the same class, no more than X lessons of the same subject per day, or similar restriction on course frequency per day.')
            ->requiredFields(['max_frequency'])
            ->optionalFields(['course_exceptions'])
            ->howToUse([
                'max_frequency is always required and applies as the default maximum number of times any single course can be scheduled on any day.',
                'Do NOT create the course_exceptions array unless the user explicitly names one or more specific courses that should be allowed a DIFFERENT (higher or lower) daily frequency than the default.',
                'Inside course_exceptions, every item must contain exactly two fields: course_id (required – must be a valid course identifier) and max_frequency (required – integer).',
                'Never invent, assume or add course_exceptions — only include them when the user clearly specifies exceptions for named courses.',
                'The scheduler must strictly respect both the default max_frequency and any course-specific exception values — never schedule the same course more times than allowed on any given day.'
            ])
            ->examples([
                [
                    "max_frequency" => 2
                ],

                [
                    "max_frequency" => 1,
                    "course_exceptions" => [
                        [
                            "course_id" => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                            "max_frequency" => 3
                        ],
                        [
                            "course_id" => "abcdef12-3456-7890-abcd-ef1234567890",
                            "max_frequency" => 2
                        ]
                    ]
                ],

                [
                    "max_frequency" => 1
                ]
            ])
            ->build();
    }
}
