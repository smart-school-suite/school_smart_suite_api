<?php

namespace App\Constant\Timetable\Constraints\Guides\Hard;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;

class BreakPeriodGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('break_period')
            ->intent('Defines non-schedulable break/lunch periods during which no classes, sessions or activities can be placed.')
            ->whenToUse('Use this constraint when the user mentions lunch break, recess, mandatory daily pauses, no-class periods, break time between 12-1, or any fixed non-teaching interval that must be kept free of scheduling.')
            ->requiredFields(['start_time', 'end_time'])
            ->optionalFields(['no_break_exceptions', 'day_exceptions'])
            ->howToUse([
                'start_time and end_time are ALWAYS required and define the default break period that applies to every operational day unless overridden.',
                'The break blocks ALL scheduling (classes, exams, activities) during the specified time window — treat it as a hard exclusion zone.',
                'no_break_exceptions (optional array of day names) completely removes the break on the listed days — no break period exists on those days.',
                'day_exceptions (optional array of objects) overrides the default break time for specific days with a different start_time/end_time (or potentially none, but usually different timing).',
                'Inside day_exceptions, every object must contain: "day" (required – valid day name like "monday") and BOTH "start_time" and "end_time" (required).',
                'Do NOT create no_break_exceptions or day_exceptions unless the user explicitly mentions exceptions for certain days (e.g. "no lunch on Monday", "shorter break on Friday").',
                'Never invent day names or times — only use exactly what the user specifies.',
                'If both no_break_exceptions and day_exceptions are present, no_break_exceptions take precedence (completely remove break), then day_exceptions override timing on remaining days.',
            ])
            ->examples([
                [
                    "start_time" => "12:00",
                    "end_time" => "13:00"
                ],

                [
                    "start_time" => "15:00",
                    "end_time" => "15:30",
                    "day_exceptions" => [
                        [
                            "day" => "friday",
                            "start_time" => "14:00",
                            "end_time" => "15:00"
                        ]
                    ]
                ],

                [
                    "start_time" => "12:00",
                    "end_time" => "13:00",
                    "no_break_exceptions" => ["monday", "wednesday"],
                    "day_exceptions" => [
                        [
                            "day" => "friday",
                            "start_time" => "14:00",
                            "end_time" => "15:00"
                        ]
                    ]
                ],

                [
                    "start_time" => "12:30",
                    "end_time" => "13:30",
                    "no_break_exceptions" => ["saturday"]
                ]
            ])
            ->build();
    }
}
