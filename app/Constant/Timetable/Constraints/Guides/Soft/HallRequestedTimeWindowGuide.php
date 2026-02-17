<?php

namespace App\Constant\Timetable\Constraints\Guides\Soft;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;
class HallRequestedTimeWindowGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('hall_requested_time_windows')
            ->intent('Specifies preferred days and/or time windows where the user would like activities to be scheduled in a specific hall/room. These are soft preferences — the scheduler should try to honor them when possible, but may place activities outside these windows if hard constraints (hall availability, conflicts, etc.) prevent it.')
            ->whenToUse('Use this constraint ONLY when the user explicitly requests or suggests preferred times/days for a hall/room, such as "use lab A on Monday mornings", "schedule in room B preferably in the afternoon", "hall C should be used between 8 and 6", "try to book gym on Wednesday", or any indication of desired time windows for one or more halls.')
            ->requiredFields(['hall_id', 'windows'])
            ->optionalFields(['windows.day', 'windows.start_time', 'windows.end_time'])
            ->howToUse([
                'The value must ALWAYS be an array of objects, each containing exactly one "hall_id" and one "windows" key.',
                'Inside each object, "hall_id" is REQUIRED (string/UUID).',
                '"windows" is REQUIRED and must be a non-empty array of window objects.',
                'Each window object can be one of three valid forms — exactly one of these patterns must be followed per window:',

                '1. Day + time range (most complete): { "day": "monday", "start_time": "08:00", "end_time": "18:00" }',
                '   → specific day and broad/narrow time window',

                '2. Day only (prefers the day, any valid time): { "day": "tuesday" }',
                '   → day is present, but NO start_time and NO end_time',

                '3. Time range only (prefers the time window, any day): { "start_time": "08:00", "end_time": "18:00" }',
                '   → start_time and end_time are present, but NO day',

                'Never generate a window object that has neither day nor time range — every window must have at least one of: day OR (start_time + end_time).',
                'Do NOT add start_time/end_time unless the user explicitly mentions a time preference (morning, afternoon, specific hours, opening hours, etc.).',
                'Do NOT add day unless the user explicitly mentions a day preference (Monday, Tuesday, etc.).',
                'Never invent or assume values for day, start_time, or end_time — only use what the user clearly states.',
                'Multiple windows per hall are allowed and encouraged when the user gives several preferred slots.',
                'This is a SOFT preference — do NOT treat it as a hard constraint; always allow the scheduler to deviate when necessary and explain deviations in diagnostics if used.'
            ])
            ->examples([
                [
                    [
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "windows" => [
                            [
                                "day" => "monday",
                                "start_time" => "08:00",
                                "end_time" => "18:00"
                            ],
                            [
                                "day" => "tuesday",
                                "start_time" => "08:00",
                                "end_time" => "18:00"
                            ]
                        ]
                    ]
                ],

                [
                    [
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "windows" => [
                            ["day" => "monday"],
                            [
                                "day" => "tuesday",
                                "start_time" => "08:00",
                                "end_time" => "18:00"
                            ]
                        ]
                    ]
                ],

                [
                    [
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "windows" => [
                            [
                                "start_time" => "08:00",
                                "end_time" => "18:00"
                            ]
                        ]
                    ]
                ],

                [
                    [
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "windows" => [["day" => "wednesday"], ["day" => "thursday"]]
                    ],
                    [
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "windows" => [
                            ["start_time" => "14:00", "end_time" => "17:00"]
                        ]
                    ]
                ],
                [
                    [
                        "hall_id" => "room101",
                        "windows" => [["day" => "friday"]]
                    ]
                ]
            ])
            ->build();
    }
}
