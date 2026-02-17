<?php

namespace App\Constant\Timetable\Constraints\Guides\Hard;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;

class OperationalPeriodGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('operational_period')
            ->intent('Defines the institution\'s overall operational hours (opening to closing time) outside of which no classes, sessions, or activities can be scheduled.')
            ->whenToUse('Use this constraint when the user specifies the school\'s daily operating hours, opening and closing times, timetable window, "classes only between 7am and 6pm", "school runs from 8 to 5", or any definition of the allowed scheduling timeframe for the entire institution.')
            ->requiredFields(['start_time', 'end_time'])
            ->optionalFields(['day_exceptions'])
            ->howToUse([
                'start_time and end_time are ALWAYS required and define the default operational window that applies to every operational day unless overridden.',
                'No scheduling (classes, exams, activities, breaks, etc.) is allowed outside of the defined start_time to end_time — treat the time before start_time and after end_time as hard non-operational zones.',
                'day_exceptions (optional array of objects) overrides the default operational hours for specific days with different start_time and/or end_time.',
                'Inside day_exceptions, every object must contain: "day" (required – valid day name like "monday", "wednesday", etc.) and BOTH "start_time" and "end_time" (required).',
                'Do NOT create day_exceptions unless the user explicitly mentions different hours for certain days (e.g. "half day on Wednesday", "school closes early on Friday").',
                'Never invent day names or times — only use exactly what the user specifies.',
                'start_time must always be earlier than end_time in every case (default and exceptions).',
                'This is a HARD constraint — the scheduler must never place any activity outside the defined operational period on any day.'
            ])
            ->examples([
                [
                    "start_time" => "07:00",
                    "end_time" => "18:00"
                ],

                [
                    "start_time" => "08:00",
                    "end_time" => "17:00",
                    "day_exceptions" => [
                        [
                            "day" => "wednesday",
                            "start_time" => "09:00",
                            "end_time" => "16:00"
                        ]
                    ]
                ],
                [
                    "start_time" => "07:30",
                    "end_time" => "16:30",
                    "day_exceptions" => [
                        [
                            "day" => "friday",
                            "start_time" => "07:30",
                            "end_time" => "14:00"
                        ],
                        [
                            "day" => "saturday",
                            "start_time" => "08:00",
                            "end_time" => "13:00"
                        ]
                    ]
                ],

                // Minimal with one exception
                [
                    "start_time" => "08:00",
                    "end_time" => "15:00",
                    "day_exceptions" => [
                        [
                            "day" => "monday",
                            "start_time" => "07:30",
                            "end_time" => "16:30"
                        ]
                    ]
                ]
            ])
            ->build();
    }
}
