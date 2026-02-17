<?php

namespace App\Constant\Timetable;

class HardTimetableConstraints
{
    private const DAYS_OF_WEEK = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday'
    ];

    public static function getDefaultJson(): string
    {
        $defaults = [
            "hard_constraints" => [
                "break_period" => [
                    "start_time" => "12:00",
                    "end_time" => "13:00",
                    "no_break_exceptions" => [
                        "monday"
                    ],
                    "day_exceptions" => [
                        [
                            "day" => "friday",
                            "start_time" => "14:00",
                            "end_time" => "15:00"
                        ]
                    ]
                ],
                "operational_period" => [
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
                "schedule_period_duration_minutes" => [
                    "duration_minutes" => 60,
                    "day_exceptions" => [
                        [
                            "day" => "tuesday",
                            "duration_minutes" => 120
                        ]
                    ]
                ],
                "required_joint_course_periods" => [
                    [
                        "course_id" => "00d6c93b-4bf9-4634-adc2-293f3c513c18",
                        "teacher_id" => "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
                        "periods" => [
                            [
                                "day" => "monday",
                                "start_time" => "10:00",
                                "end_time" => "11:00"
                            ],
                            [
                                "day" => "wednesday",
                                "start_time" => "14:00",
                                "end_time" => "15:00"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function getJsonSchema(): string
    {
        $schema = [
            "\$schema" => "https://json-schema.org/draft/2020-12/schema",
            "title" => "Hard Timetable Constraints",
            "type" => "object",
            "required" => ["hard_constraints"],
            "properties" => [
                "hard_constraints" => [
                    "type" => "object",
                    "required" => ["break_period", "operational_period", "periods"],
                    "properties" => [
                        "break_period" => [
                            "type" => "object",
                            "description" => "Configure when AI should schedule breaks in your calendar. Set a default break time that applies across all days, with optional exceptions for specific days.",
                            "required" => ["start_time", "end_time"],
                            "properties" => [
                                "start_time" => [
                                    "type" => "string",
                                    "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$",
                                    "description" => "Tell AI when your default break starts (24-hour format)",
                                    "example" => "12:00"
                                ],
                                "end_time" => [
                                    "type" => "string",
                                    "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$",
                                    "description" => "Tell AI when your default break ends (24-hour format)",
                                    "example" => "13:00"
                                ],
                                "no_break_exceptions" => [
                                    "type" => ["array", "null"],
                                    "description" => "OPTIONAL: List specific days when AI should skip your break entirely. Only include this if you work through breaks on certain days.",
                                    "items" => [
                                        "type" => "string",
                                        "enum" => self::DAYS_OF_WEEK
                                    ],
                                    "minItems" => 1,
                                    "uniqueItems" => true,
                                    "example" => ["monday", "wednesday"]
                                ],
                                "day_exceptions" => [
                                    "type" => ["array", "null"],
                                    "description" => "OPTIONAL: Instruct AI to use different break times on specific days. Only include this if certain days have custom break schedules.",
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["day", "start_time", "end_time"],
                                        "properties" => [
                                            "day" => [
                                                "type" => "string",
                                                "enum" => self::DAYS_OF_WEEK,
                                                "description" => "Which day has a different break time"
                                            ],
                                            "start_time" => [
                                                "type" => "string",
                                                "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$",
                                                "description" => "When AI should start the break on this day"
                                            ],
                                            "end_time" => [
                                                "type" => "string",
                                                "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$",
                                                "description" => "When AI should end the break on this day"
                                            ]
                                        ],
                                        "additionalProperties" => false
                                    ],
                                    "minItems" => 1,
                                    "example" => [
                                        [
                                            "day" => "friday",
                                            "start_time" => "14:00",
                                            "end_time" => "15:00"
                                        ]
                                    ]
                                ]
                            ],
                            "additionalProperties" => false
                        ],
                        "operational_period" => [
                            "type" => "object",
                            "description" => "Define your working hours so AI knows when to schedule appointments and activities. Set your default daily schedule with optional variations for specific days.",
                            "required" => ["start_time", "end_time"],
                            "properties" => [
                                "start_time" => [
                                    "type" => "string",
                                    "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$",
                                    "description" => "Tell AI when your workday typically starts (24-hour format)",
                                    "example" => "08:00"
                                ],
                                "end_time" => [
                                    "type" => "string",
                                    "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$",
                                    "description" => "Tell AI when your workday typically ends (24-hour format)",
                                    "example" => "17:00"
                                ],
                                "day_exceptions" => [
                                    "type" => ["array", "null"],
                                    "description" => "OPTIONAL: Instruct AI about days with different working hours. Only include this if certain days have custom schedules.",
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["day", "start_time", "end_time"],
                                        "properties" => [
                                            "day" => [
                                                "type" => "string",
                                                "enum" => self::DAYS_OF_WEEK,
                                                "description" => "Which day has different operational hours"
                                            ],
                                            "start_time" => [
                                                "type" => "string",
                                                "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$",
                                                "description" => "When AI should consider you available on this day"
                                            ],
                                            "end_time" => [
                                                "type" => "string",
                                                "pattern" => "^([01][0-9]|2[0-3]):[0-5][0-9]$",
                                                "description" => "When AI should consider you unavailable on this day"
                                            ]
                                        ],
                                        "additionalProperties" => false
                                    ],
                                    "minItems" => 1,
                                    "example" => [
                                        [
                                            "day" => "wednesday",
                                            "start_time" => "09:00",
                                            "end_time" => "16:00"
                                        ],
                                        [
                                            "day" => "friday",
                                            "start_time" => "08:00",
                                            "end_time" => "15:00"
                                        ]
                                    ]
                                ]
                            ],
                            "additionalProperties" => false
                        ],
                        "schedule_period_duration_minutes" => [
                            "type" => "object",
                            "description" => "Guide AI on how long each appointment or time block should be by default. Set a standard duration with optional variations for specific days of the week.",
                            "required" => ["duration_minutes"],
                            "properties" => [
                                "duration_minutes" => [
                                    "type" => "integer",
                                    "minimum" => 1,
                                    "description" => "Tell AI the default length of each scheduled period in minutes",
                                    "example" => 60
                                ],
                                "day_exceptions" => [
                                    "type" => ["array", "null"],
                                    "description" => "OPTIONAL: Instruct AI about days that require different duration lengths. Only include this if certain days need custom time blocks.",
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["day", "duration_minutes"],
                                        "properties" => [
                                            "day" => [
                                                "type" => "string",
                                                "enum" => self::DAYS_OF_WEEK,
                                                "description" => "Which day has a different duration setting"
                                            ],
                                            "duration_minutes" => [
                                                "type" => "integer",
                                                "minimum" => 1,
                                                "description" => "How long AI should make each scheduled period on this day (in minutes)"
                                            ]
                                        ],
                                        "additionalProperties" => false
                                    ],
                                    "minItems" => 1,
                                    "example" => [
                                        [
                                            "day" => "tuesday",
                                            "duration_minutes" => 120
                                        ],
                                        [
                                            "day" => "friday",
                                            "duration_minutes" => 45
                                        ]
                                    ]
                                ]
                            ],
                            "additionalProperties" => false
                        ]
                    ],
                    "additionalProperties" => false
                ]
            ],
            "additionalProperties" => false
        ];

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function getDefaultArray(): array
    {
        return json_decode(self::getDefaultJson(), true)['hard_constraints'];
    }
}
