<?php

namespace App\Constant\Timetable\Constraints\Guides\Soft;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;

class RequestedFreePeriodGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('requested_free_periods')
            ->intent('Specifies preferred days and/or time windows where the user would like free periods (gaps, breaks, empty slots) to appear in the student timetable(s). These are soft preferences — the scheduler should try to create these free slots when possible, but may fill them or adjust timings if hard constraints (course requirements, teacher availability, room conflicts, max free periods limits, etc.) prevent it.')
            ->whenToUse('Use this constraint ONLY when the user explicitly requests or suggests desired free periods/gaps/breaks in the student schedule, such as "leave a free period on Monday at 10", "students should have a break from 12-1 on Wednesday", "try to give an empty slot Friday morning", "I want lunch break at noon", or any indication of preferred empty time windows in the daily timetable.')
            ->requiredFields([])
            ->optionalFields([])
            ->howToUse([
                'The value must ALWAYS be an array of free-period request objects (can be empty, but usually non-empty when used).',
                'Each object represents one requested free period and can be one of three valid forms — exactly one of these patterns must be followed per request:',

                '1. Day + time range (most precise): { "day": "monday", "start_time": "10:00", "end_time": "11:00" }',
                '   → specific day and exact time window for the free period',

                '2. Day only (prefers a free period on that day, any valid time): { "day": "friday" }',
                '   → day is present, but NO start_time and NO end_time',

                '3. Time range only (prefers a free period at that time, any day): { "start_time": "12:00", "end_time": "13:00" }',
                '   → start_time and end_time are present, but NO day',

                'Never generate a request object that has neither day nor time range — every free-period request must have at least one of: day OR (start_time + end_time).',
                'Do NOT add start_time/end_time unless the user explicitly mentions a preferred time or time window for the free period (e.g. lunch at noon, break at 10, morning gap).',
                'Do NOT add day unless the user explicitly mentions a preferred day for the free period.',
                'Never invent or assume values for day, start_time, or end_time — only use what the user clearly states.',
                'Multiple free-period requests are allowed and common when the user wants several specific gaps.',
                'This is a SOFT preference — do NOT treat it as a hard constraint; always allow the scheduler to deviate when necessary (e.g. due to max_free_periods_per_day, teacher continuity rules, etc.) and explain deviations in diagnostics if used.'
            ])
            ->examples([
                [
                    [
                        "day" => "monday",
                        "start_time" => "10:00",
                        "end_time" => "11:00"
                    ],
                    [
                        "day" => "friday",
                        "start_time" => "10:00",
                        "end_time" => "11:00"
                    ]
                ],

                [
                    [
                        "day" => "monday"
                    ],
                    [
                        "day" => "friday",
                        "start_time" => "10:00",
                        "end_time" => "11:00"
                    ]
                ],

                [
                    [
                        "start_time" => "12:00",
                        "end_time" => "13:00"
                    ]
                ],

                [
                    [
                        "day" => "wednesday"
                    ],
                    [
                        "day" => "thursday",
                        "start_time" => "11:30",
                        "end_time" => "12:30"
                    ],
                    [
                        "start_time" => "14:00",
                        "end_time" => "15:00"
                    ]
                ],

                [
                    [
                        "day" => "tuesday"
                    ]
                ]
            ])
            ->build();
    }
}
