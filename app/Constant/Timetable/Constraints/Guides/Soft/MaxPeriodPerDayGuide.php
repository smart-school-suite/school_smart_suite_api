<?php

namespace App\Constant\Timetable\Constraints\Guides\Soft;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;

class MaxPeriodPerDayGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('schedule_max_periods_per_day')
            ->intent('Sets the maximum number of class periods/sessions that can be scheduled on any single day for the entire school.')
            ->whenToUse('Use this constraint only when the user mentions a maximum number of periods per day, daily timetable length limit, maximum lessons/classes per day, or similar restriction on the daily schedule structure.')
            ->requiredFields(['max_periods'])
            ->optionalFields(['day_exceptions'])
            ->howToUse([
                'max_periods is always required and applies as the default maximum number of periods per day for ALL days.',
                'Do NOT create the day_exceptions array unless the user explicitly names one or more specific days that should allow a DIFFERENT (higher or lower) number of periods than the default.',
                'Inside day_exceptions, every item must contain exactly two fields: day (required – must be a valid day name like "monday", "tuesday", etc.) and max_periods (required – integer).',
                'Never invent, assume or add day_exceptions — only include them when the user clearly specifies exceptions for named days.',
                'The scheduler must strictly respect both the default max_periods and any day-specific exception values — never schedule more periods than allowed on any given day.'
            ])
            ->examples([
                [
                    "max_periods" => 6
                ],
                [
                    "max_periods" => 5,
                    "day_exceptions" => [
                        [
                            "day" => "monday",
                            "max_periods" => 7
                        ],
                        [
                            "day" => "friday",
                            "max_periods" => 4
                        ]
                    ]
                ],
                [
                    "max_periods" => 8
                ]
            ])
            ->build();
    }
}
