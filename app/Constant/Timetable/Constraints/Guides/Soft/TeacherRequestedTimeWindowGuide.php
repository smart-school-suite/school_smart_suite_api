<?php

namespace App\Constant\Timetable\Constraints\Guides\Soft;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;

class TeacherRequestedTimeWindowGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('teacher_requested_time_windows')
            ->intent('Specifies preferred days and/or time windows where the user would like a specific teacher to teach/hold classes. These are soft preferences — the scheduler should try to honor them when possible, but may assign teaching sessions outside these windows if hard constraints (teacher availability, course requirements, conflicts, etc.) prevent it.')
            ->whenToUse('Use this constraint ONLY when the user explicitly requests or suggests preferred teaching times/days for a teacher, such as "Mr. X prefers mornings on Monday", "schedule teacher Y only in the afternoon", "give teacher Z classes between 8 and 12 on Wednesday", "avoid late slots for this teacher", or any indication of desired time windows for one or more teachers.')
            ->requiredFields(['teacher_id', 'windows'])
            ->optionalFields(['day', 'start_time', 'end_time'])
            ->howToUse([
                'The value must ALWAYS be an array of objects, each containing exactly one "teacher_id" and one "windows" key.',
                'Inside each object, "teacher_id" is REQUIRED (string/UUID).',
                '"windows" is REQUIRED and must be a non-empty array of window objects.',
                'Each window object can be one of three valid forms — exactly one of these patterns must be followed per window:',

                '1. Day + time range (most complete): { "day": "monday", "start_time": "08:00", "end_time": "12:00" }',
                '   → specific day and time window',

                '2. Day only (prefers the day, any valid time): { "day": "wednesday" }',
                '   → day is present, but NO start_time and NO end_time',

                '3. Time range only (prefers the time window, any day): { "start_time": "08:00", "end_time": "18:00" }',
                '   → start_time and end_time are present, but NO day',

                'Never generate a window object that has neither day nor time range — every window must have at least one of: day OR (start_time + end_time).',
                'Do NOT add start_time/end_time unless the user explicitly mentions a time preference (mornings, afternoons, specific hours, avoid evenings, etc.).',
                'Do NOT add day unless the user explicitly mentions a day preference (Monday, Tuesday, etc.).',
                'Never invent or assume values for day, start_time, or end_time — only use what the user clearly states.',
                'Multiple windows per teacher are allowed and encouraged when the user gives several preferred slots.',
                'This is a SOFT preference — do NOT treat it as a hard constraint; always allow the scheduler to deviate when necessary and explain deviations in diagnostics if used.'
            ])
            ->examples([
                [
                    [
                        "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                        "windows" => [
                            [
                                "day" => "monday",
                                "start_time" => "08:00",
                                "end_time" => "12:00"
                            ],
                            [
                                "day" => "wednesday",
                                "start_time" => "13:00",
                                "end_time" => "17:00"
                            ]
                        ]
                    ]
                ],

                [
                    [
                        "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "windows" => [
                            ["day" => "monday"]
                        ]
                    ]
                ],

                [
                    [
                        "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
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
                        "teacher_id" => "teacher-math-uuid",
                        "windows" => [["day" => "tuesday"], ["day" => "thursday"]]
                    ],
                    [
                        "teacher_id" => "teacher-physics-uuid",
                        "windows" => [
                            [
                                "day" => "friday",
                                "start_time" => "09:00",
                                "end_time" => "14:00"
                            ]
                        ]
                    ]
                ],

                [
                    [
                        "teacher_id" => "teacherX-uuid",
                        "windows" => [["day" => "friday"]]
                    ]
                ]
            ])
            ->build();
    }
}
