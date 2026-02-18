<?php

namespace App\Constant\Timetable\Constraints\Guides\Hard;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;

class PeriodDurationGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make(key: 'schedule_period_duration_minutes')
            ->intent('Defines the standard length (in minutes) of each class period/slot across the timetable.')
            ->whenToUse('Use this constraint when the user specifies the length of periods, "each class is 60 minutes", "periods are 45 minutes long", "double periods on some days", "lessons last 90 minutes", or any statement about the fixed or default duration of timetable slots.')
            ->requiredFields(['duration_minutes'])
            ->optionalFields(['day_exceptions'])
            ->howToUse([
                'duration_minutes is ALWAYS required and sets the default period length (in whole minutes) that applies to every operational day unless overridden.',
                'All regular periods/slots must be exactly this length unless a day exception changes it for that specific day.',
                'day_exceptions (optional array of objects) overrides the default duration for specific days with a different period length.',
                'Inside day_exceptions, every object must contain exactly two fields: "day" (required – valid day name like "monday", "tuesday", etc.) and "duration_minutes" (required – positive integer).',
                'Do NOT create day_exceptions unless the user explicitly mentions a different period length for certain days (e.g. "longer periods on Tuesday", "45-minute slots on Friday").',
                'Never invent day names or duration values — only use exactly what the user specifies.',
                'duration_minutes should always be a positive integer (typically 30–120 range in school contexts).',
                'This is a HARD constraint — the scheduler must generate all periods exactly matching the specified duration (default or day-specific) — no variation allowed unless explicitly excepted.'
            ])
            ->examples([
                [
                    "duration_minutes" => 60
                ],

                [
                    "duration_minutes" => 90,
                    "day_exceptions" => [
                        [
                            "day" => "tuesday",
                            "duration_minutes" => 120
                        ]
                    ]
                ],

                [
                    "duration_minutes" => 45,
                    "day_exceptions" => [
                        [
                            "day" => "monday",
                            "duration_minutes" => 60
                        ],
                        [
                            "day" => "friday",
                            "duration_minutes" => 30
                        ]
                    ]
                ],

                [
                    "duration_minutes" => 50,
                    "day_exceptions" => [
                        [
                            "day" => "thursday",
                            "duration_minutes" => 100
                        ]
                    ]
                ]
            ])
            ->build();
    }
}
