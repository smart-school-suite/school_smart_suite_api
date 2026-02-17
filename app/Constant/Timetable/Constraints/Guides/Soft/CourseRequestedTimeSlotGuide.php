<?php

namespace App\Constant\Timetable\Constraints\Guides\Soft;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;

class CourseRequestedTimeSlotGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('course_requested_time_slots')
            ->intent('Specifies preferred days and/or time slots where the user would like specific courses to be scheduled. These are soft preferences — the scheduler should try to honor them when possible, but may place sessions elsewhere if hard constraints (availability, conflicts, teacher hours, etc.) prevent it.')
            ->whenToUse('Use this constraint ONLY when the user explicitly requests preferred days, preferred time slots, preferred periods, "best if on …", "would like classes in the morning", "try to put math on Monday", or any other indication of desired scheduling times/days for one or more courses.')
            ->requiredFields(['course_id', 'slots'])
            ->optionalFields(['slots.day', 'slots.start_time', 'slots.end_time'])
            ->howToUse([
                'The value must ALWAYS be an array of objects, each containing exactly one "course_id" and one "slots" key.',
                'Inside each object, "course_id" is REQUIRED (string/UUID).',
                '"slots" is REQUIRED and must be a non-empty array of slot objects.',
                'Each slot object can be one of three valid forms — exactly one of these patterns must be followed per slot:',

                '1. Day + time range (most complete): { "day": "monday", "start_time": "09:00", "end_time": "10:30" }',
                '   → both day and time range are specified',

                '2. Day only (prefers the day, any valid time): { "day": "wednesday" }',
                '   → day is present, but NO start_time and NO end_time',

                '3. Time range only (prefers the time window, any day): { "start_time": "08:00", "end_time": "10:30" }',
                '   → start_time and end_time are present, but NO day',

                'Never generate a slot object that has neither day nor time range — every slot must have at least one of: day OR (start_time + end_time).',
                'Do NOT add start_time/end_time unless the user explicitly mentions a time preference (morning, afternoon, specific hours, etc.).',
                'Do NOT add day unless the user explicitly mentions a day preference (Monday, Tuesday, etc.).',
                'Never invent or assume values for day, start_time, or end_time — only use what the user clearly states.',
                'Multiple slots per course are allowed and encouraged when the user gives several preferences.',
                'This is a SOFT preference — do NOT treat it as a hard constraint; always allow the scheduler to deviate when necessary and explain deviations in diagnostics if used.'
            ])
            ->examples([
                [
                    [
                        "course_id" => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "slots" => [
                            [
                                "day" => "monday",
                                "start_time" => "09:00",
                                "end_time" => "10:30"
                            ],
                            [
                                "day" => "wednesday",
                                "start_time" => "14:00",
                                "end_time" => "15:30"
                            ]
                        ]
                    ]
                ],
                [
                    [
                        "course_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "slots" => [
                            ["day" => "monday"],
                            [
                                "day" => "wednesday",
                                "start_time" => "14:00",
                                "end_time" => "15:30"
                            ]
                        ]
                    ]
                ],

                [
                    [
                        "course_id" => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "slots" => [
                            [
                                "start_time" => "08:00",
                                "end_time" => "10:30"
                            ]
                        ]
                    ]
                ],

                [
                    [
                        "course_id" => "math101",
                        "slots" => [["day" => "tuesday"], ["day" => "thursday"]]
                    ],
                    [
                        "course_id" => "physics202",
                        "slots" => [
                            ["start_time" => "09:00", "end_time" => "10:45"]
                        ]
                    ]
                ],

                [
                    [
                        "course_id" => "english301",
                        "slots" => [["day" => "friday"]]
                    ]
                ]
            ])
            ->build();
    }
}
