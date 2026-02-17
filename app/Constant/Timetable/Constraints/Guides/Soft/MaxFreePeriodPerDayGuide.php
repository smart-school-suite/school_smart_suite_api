<?php

namespace App\Constant\Timetable\Constraints\Guides\Soft;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;

class MaxFreePeriodPerDayGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('schedule_max_free_periods_per_day')
            ->intent('Sets the maximum number of unscheduled (free/gap) periods allowed per day in the student timetable.')
            ->whenToUse('Use this constraint only when the user mentions a maximum number of free periods per day, limit on gaps in the student schedule, maximum empty slots per day, or similar restriction on free time in the daily timetable.')
            ->requiredFields(['max_free_periods'])
            ->optionalFields(['day_exceptions'])
            ->howToUse([
                'max_free_periods is always required and applies as the default maximum number of free periods per day for ALL days and ALL students.',
                'Do NOT create the day_exceptions array unless the user explicitly names one or more specific days that should allow a DIFFERENT (higher or lower) number of free periods than the default.',
                'Inside day_exceptions, every item must contain exactly two fields: day (required – must be a valid day name like "monday", "tuesday", etc.) and max_free_periods (required – integer).',
                'Never invent, assume or add day_exceptions — only include them when the user clearly specifies exceptions for named days.',
                'The scheduler must strictly respect both the default max_free_periods and any day-specific exception values — never allow more free periods than permitted on any given day.'
            ])
            ->examples([
                [
                    "max_free_periods" => 2
                ],

                [
                    "max_free_periods" => 1,
                    "day_exceptions" => [
                        [
                            "day" => "monday",
                            "max_free_periods" => 3
                        ],
                        [
                            "day" => "friday",
                            "max_free_periods" => 0
                        ]
                    ]
                ],

                [
                    "max_free_periods" => 0
                ]
            ])
            ->build();
    }
}
