<?php

namespace App\Constant\Timetable;

class SoftTimetableConstraints
{
    public static function getDefaultJson(): string
    {
        $defaults = [
            "soft_constraints" => [
                "teacher_max_daily_hours" => 7.5,
                "teacher_max_weekly_hours" => 35.0,
                "teacher_minimum_break_minutes" => 15,
                "room_type_suitability" => [
                    "theory" => "lecture",
                    "practical" => "lab"
                ],
                "course_preferred_time_of_day" => [
                    "theory" => "morning",
                    "practical" => "afternoon"
                ],
                "time_max_periods_per_day" => 8,
                "time_min_free_periods_per_day" => 1,
                "time_consecutive_period_allowance" => [
                    "practicals" => true,
                    "theory" => true
                ],
                "time_min_gap_between_sessions_hours" => 1.0,
                "time_max_subject_frequency_per_day" => 2
            ]
        ];

        return json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    public static function getJsonSchema(): string
    {
        $schema = [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'title' => 'Soft Timetable Constraints',
            'type' => 'object',
            'required' => ['soft_constraints'],
            'properties' => [
                'soft_constraints' => [
                    'type' => 'object',
                    'properties' => [
                        'teacher_max_daily_hours' => [
                            'type' => 'number',
                            'minimum' => 1,
                            'maximum' => 12,
                            'description' => 'Maximum teaching hours per day for a teacher (e.g., 6.0–8.0).',
                            'example' => 8.0
                        ],
                        'teacher_max_weekly_hours' => [
                            'type' => 'number',
                            'minimum' => 10,
                            'maximum' => 60,
                            'description' => 'Maximum teaching hours per week (e.g., 30.0–40.0).',
                            'example' => 40.0
                        ],
                        'teacher_minimum_break_minutes' => [
                            'type' => 'integer',
                            'minimum' => 5,
                            'maximum' => 60,
                            'description' => 'Minimum break in minutes between two classes for the same teacher (e.g., 10, 15, 30).',
                            'example' => 15
                        ],
                        'room_type_suitability' => [
                            'type' => 'object',
                            'properties' => [
                                'theory' => [
                                    'type' => 'string',
                                    'enum' => ['lecture', 'any'],
                                    'description' => 'Suitable room type for theory.',
                                    'example' => 'lecture'
                                ],
                                'practical' => [
                                    'type' => 'string',
                                    'enum' => ['lab', 'any'],
                                    'description' => 'Suitable room type for practicals.',
                                    'example' => 'lab'
                                ]
                            ],
                            'required' => ['theory', 'practical'],
                            'additionalProperties' => false
                        ],
                        'course_preferred_time_of_day' => [
                            'type' => 'object',
                            'properties' => [
                                'theory' => [
                                    'type' => 'string',
                                    'enum' => ['morning', 'afternoon', 'evening', 'any'],
                                    'description' => 'Preferred time slot for theory.',
                                    'example' => 'morning'
                                ],
                                'practical' => [
                                    'type' => 'string',
                                    'enum' => ['morning', 'afternoon', 'evening', 'any'],
                                    'description' => 'Preferred time slot for practicals.',
                                    'example' => 'afternoon'
                                ]
                            ],
                            'required' => ['theory', 'practical'],
                            'additionalProperties' => false
                        ],
                        'time_max_periods_per_day' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 12,
                            'description' => 'Maximum number of teaching periods in a day (e.g., 8).',
                            'example' => 8
                        ],
                        'time_min_free_periods_per_day' => [
                            'type' => 'integer',
                            'minimum' => 0,
                            'maximum' => 6,
                            'description' => 'Minimum number of free periods per day for students/teachers (e.g., 1).',
                            'example' => 1
                        ],
                        'time_consecutive_period_allowance' => [
                            'type' => 'object',
                            'properties' => [
                                'practicals' => [
                                    'type' => 'boolean',
                                    'description' => 'true = allow consecutive periods for practicals (common in labs).',
                                    'example' => true
                                ],
                                'theory' => [
                                    'type' => 'boolean',
                                    'description' => 'true = allow double periods for theory classes.',
                                    'example' => true
                                ]
                            ],
                            'required' => ['practicals', 'theory'],
                            'additionalProperties' => false
                        ],
                        'time_min_gap_between_sessions_hours' => [
                            'type' => 'number',
                            'minimum' => 0.5,
                            'maximum' => 48.0,
                            'description' => 'Minimum gap in hours between two sessions of the same subject (e.g., 1.0 for short, 24.0 for one-day gap).',
                            'example' => 1.0
                        ],
                        'time_max_subject_frequency_per_day' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 5,
                            'description' => 'Maximum times a subject can appear in one day (e.g., 1 or 2).',
                            'example' => 2
                        ],
                        'course_fixed_time_slots' => [
                            'type' => 'array',
                            'description' => 'Courses that must be scheduled at very specific days and time slots. These act as high-priority or near-hard constraints — the solver will try to respect them exactly and will report violations or infeasibility if impossible. Useful for lecturer preferences, reserved labs, or recurring events.',
                            'items' => [
                                'type' => 'object',
                                'required' => ['course_id', 'fixed_slots'],
                                'properties' => [
                                    'course_id' => [
                                        'type' => 'string',
                                        'description' => 'Unique identifier for the course or course section (must match your internal ID format).',
                                        'example' => 'CS101-Lecture-GroupA'
                                    ],
                                    'course_name' => [
                                        'type' => 'string',
                                        'description' => 'Human-readable name of the course (informational — used for debugging, validation messages, or UI display only; not used for matching).',
                                        'example' => 'Introduction to Computer Science'
                                    ],
                                    'fixed_slots' => [
                                        'type' => 'array',
                                        'minItems' => 1,
                                        'description' => 'One or more exact time slots this course instance must occupy. For multi-session courses, provide one slot per session (they will be assigned in order). Slots must align with your timetable grid periods.',
                                        'items' => [
                                            'type' => 'object',
                                            'required' => ['day', 'start_time', 'end_time'],
                                            'properties' => [
                                                'day' => [
                                                    'type' => 'string',
                                                    'enum' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                                                    'description' => 'Day of the week (full English name, case-sensitive).'
                                                ],
                                                'start_time' => [
                                                    'type' => 'string',
                                                    'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                                                    'description' => 'Start time in 24-hour HH:MM format (leading zero optional). Must match a valid period start in your timetable.',
                                                    'example' => '08:00'
                                                ],
                                                'end_time' => [
                                                    'type' => 'string',
                                                    'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                                                    'description' => 'End time in 24-hour HH:MM format (must be after start_time and align with period boundaries).',
                                                    'example' => '10:00'
                                                ]
                                            ],
                                            'additionalProperties' => false
                                        ],
                                        'uniqueItems' => true
                                    ]
                                ],
                                'additionalProperties' => false
                            ],
                            'uniqueItems' => true,
                            'additionalItems' => false
                        ],
                        'fixed_day_time_slots' => [
                            'type' => 'array',
                            'description' => 'Specific time slots on given days that must be occupied by some class. These are high-priority constraints — the solver will try to assign a course to each of these slots. Useful when users want to ensure certain times are used (e.g. "put classes on Friday afternoon").',
                            'items' => [
                                'type' => 'object',
                                'required' => ['day', 'start_time', 'end_time'],
                                'properties' => [
                                    'day' => [
                                        'type' => 'string',
                                        'enum' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                                        'description' => 'Day of the week (full English name, case-sensitive).'
                                    ],
                                    'start_time' => [
                                        'type' => 'string',
                                        'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                                        'description' => 'Start time in 24-hour HH:MM format.',
                                        'example' => '13:00'
                                    ],
                                    'end_time' => [
                                        'type' => 'string',
                                        'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                                        'description' => 'End time in 24-hour HH:MM format (must be after start_time).',
                                        'example' => '15:00'
                                    ],
                                    // Optional: allow user to suggest preferred course types or groups
                                    'preferred_for' => [
                                        'type' => 'string',
                                        'description' => 'Optional hint: preferred course type or group (e.g. "theory", "practical", "1st year", "CS department"). Not enforced, just a soft preference.',
                                        'example' => 'practical'
                                    ]
                                ],
                                'additionalProperties' => false
                            ],
                            'uniqueItems' => true
                        ],
                        'teacher_time_windows' => [
                            'type' => 'array',
                            'description' => 'Time windows for specific teachers — either allowed teaching periods (availability) or forbidden periods (unavailability). These are treated as high-priority / near-hard constraints. The solver will respect them where possible and report violations if impossible.',
                            'items' => [
                                'type' => 'object',
                                'required' => ['teacher_id', 'windows'],
                                'properties' => [
                                    'teacher_id' => [
                                        'type' => 'string',
                                        'description' => 'Unique identifier of the teacher (must match your internal teacher ID/code).',
                                        'example' => 'TCH-045'
                                    ],
                                    'teacher_name' => [
                                        'type' => 'string',
                                        'description' => 'Human-readable name (informational only — for debugging/UI/validation; not used for matching).',
                                        'example' => 'John Doe'
                                    ],
                                    'windows' => [
                                        'type' => 'array',
                                        'minItems' => 1,
                                        'description' => 'One or more time windows on specific days. Use type "allowed" for availability/preferred slots, "forbidden" for unavailability/no-classes periods.',
                                        'items' => [
                                            'type' => 'object',
                                            'required' => ['day', 'start_time', 'end_time'],
                                            'properties' => [
                                                'day' => [
                                                    'type' => 'string',
                                                    'enum' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                                                    'description' => 'Day of the week.'
                                                ],
                                                'start_time' => [
                                                    'type' => 'string',
                                                    'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                                                    'description' => 'Start of the window (24-hour HH:MM).',
                                                    'example' => '07:00'
                                                ],
                                                'end_time' => [
                                                    'type' => 'string',
                                                    'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                                                    'description' => 'End of the window (24-hour HH:MM).',
                                                    'example' => '09:30'
                                                ],
                                                'type' => [
                                                    'type' => 'string',
                                                    'enum' => ['allowed', 'forbidden'],
                                                    'default' => 'allowed',
                                                    'description' => '"allowed" = teacher may/should only teach in this window on this day; "forbidden" = no teaching allowed in this window.'
                                                ]
                                            ],
                                            'additionalProperties' => false
                                        ],
                                        'uniqueItems' => true
                                    ]
                                ],
                                'additionalProperties' => false
                            ],
                            'uniqueItems' => true
                        ],
                        'hall_time_windows' => [
                            'type' => 'array',
                            'description' => 'Time windows specifically for classes requiring "hall" rooms (large lecture halls / theory halls / high-capacity rooms). These define allowed or forbidden periods for scheduling hall-type classes. High-priority / near-hard constraints — useful for concentrating hall usage on certain days/times (e.g. "move all hall classes to Friday 10:00–18:00").',
                            'items' => [
                                'type' => 'object',
                                'required' => ['hall_type', 'windows'],
                                'properties' => [
                                    'hall_type' => [
                                        'type' => 'string',
                                        'description' => 'Identifier for the hall/room type/category (e.g. "hall", "large_lecture", "theory_hall", "auditorium"). Must match your internal room classification.',
                                        'example' => 'hall'
                                    ],
                                    'hall_name' => [
                                        'type' => 'string',
                                        'description' => 'Human-readable label for the hall type (informational only).',
                                        'example' => 'Large Lecture Halls'
                                    ],
                                    'windows' => [
                                        'type' => 'array',
                                        'minItems' => 1,
                                        'description' => 'Time windows on specific days. "allowed" = hall classes (or filtered ones) should only be scheduled inside this window; "forbidden" = no hall classes in this window.',
                                        'items' => [
                                            'type' => 'object',
                                            'required' => ['day', 'start_time', 'end_time'],
                                            'properties' => [
                                                'day' => [
                                                    'type' => 'string',
                                                    'enum' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                                                    'description' => 'Day of the week.'
                                                ],
                                                'start_time' => [
                                                    'type' => 'string',
                                                    'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                                                    'description' => 'Start of the window (24-hour HH:MM).',
                                                    'example' => '10:00'
                                                ],
                                                'end_time' => [
                                                    'type' => 'string',
                                                    'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                                                    'description' => 'End of the window (24-hour HH:MM).',
                                                    'example' => '18:00'
                                                ],
                                                'type' => [
                                                    'type' => 'string',
                                                    'enum' => ['allowed', 'forbidden'],
                                                    'default' => 'allowed',
                                                    'description' => '"allowed" = prefer/force hall classes into this window; "forbidden" = block hall classes here.'
                                                ]
                                            ],
                                            'additionalProperties' => false
                                        ],
                                        'uniqueItems' => true
                                    ]
                                ],
                                'additionalProperties' => false
                            ],
                            'uniqueItems' => true
                        ],
                        'fixed_assignments' => [
                            'type' => 'array',
                            'description' => 'Fully or partially pre-assigned timetable slots. Each entry forces a specific course (or section) to be taught by a given teacher in a given room/hall on a specific day and time window. These are treated as hard (must-respect) constraints — violations cause infeasibility or explicit conflict reports. Ideal for manual overrides, special arrangements, or very precise user requests like "put course A on Friday for teacher A using hall B".',
                            'items' => [
                                'type' => 'object',
                                'required' => ['course_id'],
                                'properties' => [
                                    'course_id' => [
                                        'type' => 'string',
                                        'description' => 'Unique identifier of the course/section being assigned.',
                                        'example' => 'CS101-L1'
                                    ],
                                    'course_name' => [
                                        'type' => 'string',
                                        'description' => 'Human-readable name (informational only).',
                                        'example' => 'Algorithms'
                                    ],
                                    'teacher_id' => [
                                        'type' => 'string',
                                        'description' => 'Unique identifier of the teacher who must teach this slot (if omitted, teacher is flexible).',
                                        'example' => 'TCH-017'
                                    ],
                                    'teacher_name' => [
                                        'type' => 'string',
                                        'description' => 'Human-readable teacher name (informational).'
                                    ],
                                    'room_id' => [
                                        'type' => 'string',
                                        'description' => 'Unique identifier of the room/hall that must be used (if omitted, room is flexible but must match course type).',
                                        'example' => 'HALL-B-204'
                                    ],
                                    'room_name' => [
                                        'type' => 'string',
                                        'description' => 'Human-readable room/hall name (informational).',
                                        'example' => 'Lecture Hall B'
                                    ],
                                    'day' => [
                                        'type' => 'string',
                                        'enum' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                                        'description' => 'Day on which this assignment must occur.'
                                    ],
                                    'start_time' => [
                                        'type' => 'string',
                                        'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                                        'description' => 'Start time (24-hour HH:MM).',
                                        'example' => '10:00'
                                    ],
                                    'end_time' => [
                                        'type' => 'string',
                                        'pattern' => '^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$',
                                        'description' => 'End time (24-hour HH:MM).'
                                    ],
                                    'notes' => [
                                        'type' => 'string',
                                        'description' => 'Optional free-text reason/comment (shown in conflict reports/UI).',
                                        'example' => 'Special guest lecture arrangement'
                                    ]
                                ],
                                'additionalProperties' => false,
                            ],
                            'uniqueItems' => true
                        ]
                    ],
                    'additionalProperties' => false
                ]
            ],
            'additionalProperties' => false
        ];

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    public static function getDefaultArray(): array
    {
        return json_decode(self::getDefaultJson(), true)['soft_constraints'];
    }

    public static function getJsonSchemaArray(): array
    {
        return json_decode(self::getJsonSchema(), true);
    }
}
