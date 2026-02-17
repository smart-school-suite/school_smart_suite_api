<?php

namespace App\Constant\Timetable;

class SoftTimetableConstraints
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
            "soft_constraints" => [
                "teacher_max_daily_hours" => [
                    "max_hours" => 8.0,
                    "teacher_exceptions" => [
                        [
                            "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                            "max_hours" => 10
                        ]
                    ]
                ],
                "teacher_max_weekly_hours" => [
                    "max_hours" => 40.0,
                    "teacher_exceptions" => [
                        [
                            "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                            "max_hours" => 25
                        ]
                    ]
                ],
                "schedule_max_periods_per_day" => [
                    "max_periods" => 8,
                    "day_exceptions" => [
                        [
                            "day" => "monday",
                            "max_periods" => 7
                        ]
                    ]
                ],
                "schedule_max_free_periods_per_day" => [
                    "max_free_periods" => 2,
                    "day_exceptions" => [
                        [
                            "day" => "monday",
                            "max_free_periods" => 3
                        ]
                    ]
                ],
                "course_max_daily_frequency" => [
                    "max_frequency" => 2,
                    "course_exceptions" => [
                        [
                            "course_id" => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                            "max_frequency" => 3
                        ]
                    ]
                ],
                "course_requested_time_slots" => [
                    [
                        "course_id" => "3e47b46a-e7df-4ac9-a29f-8fb07f92500f",
                        "course_name" => "Introduction to Computer Science",
                        "slots" => [
                            [
                                "day" => "Monday",
                                "start_time" => "08:00",
                                "end_time" => "10:00"
                            ],
                            [
                                "day" => "Wednesday",
                                "start_time" => "08:00",
                                "end_time" => "10:00"
                            ],
                            [
                                "day" => "friday"
                            ],
                            [
                                "start_time" => "14:00",
                                "end_time" => "16:00"
                            ]
                        ]
                    ]
                ],
                "requested_assignments" => [
                    [
                        "course_id" => "3e47b46a-e7df-4ac9-a29f-8fb07f92500f",
                        "course_name" => "Introduction to Computer Science",
                        "teacher_id" => "TCH-045",
                        "teacher_name" => "John Doe",
                        "room_id" => "HALL-B-204",
                        "room_name" => "Lecture Hall B",
                        "day" => "Monday",
                        "start_time" => "08:00",
                        "end_time" => "10:00"
                    ],
                    [
                        "course_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "day" => "monday"
                    ],
                    [
                        "course_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "start_time" => "14:00",
                        "end_time" => "16:00"
                    ]
                ],
                "hall_requested_time_windows" => [
                    [
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "windows" =>  [
                            [
                                "day" => "monday",
                                "start_time" => "08:00",
                                "end_time" => "18:00"
                            ],
                            [
                                "day" => "tuesday",
                                "start_time" => "08:00",
                                "end_time" => "18:00"
                            ],
                            [
                                "day" => "wednesday"
                            ],
                            [
                                "start_time" => "14:00",
                                "end_time" => "15:00"
                            ]
                        ]
                    ]
                ],
                "teacher_requested_time_windows" => [
                    [
                        "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                        "windows" =>  [
                            [
                                "day" => "monday",
                                "start_time" => "08:00",
                                "end_time" => "18:00"
                            ],
                            [
                                "day" => "tuesday",
                                "start_time" => "08:00",
                                "end_time" => "18:00"
                            ],
                            [
                                "day" => "wednesday"
                            ],
                            [
                                "start_time" => "14:00",
                                "end_time" => "15:00"
                            ]
                        ]
                    ]
                ],
                "requested_free_periods" => [
                    [
                        "day" => "monday",
                        "start_time" => "12:00",
                        "end_time" => "13:00"
                    ],
                    [
                        "start_time" => "13:00",
                        "end_time" => "14:00"
                    ]
                ]
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
                        "teacher_max_daily_hours" => [
                            "type" => "object",
                            "required" => ["max_hours"],
                            "properties" => [
                                "max_hours" => [
                                    "type" => "number",
                                    "minimum" => 0,
                                    "maximum" => 8,
                                    "description" => "Default maximum daily hours that applies to all teachers on all days"
                                ],
                                "teacher_exceptions" => [
                                    "type" => ["array", "null"],
                                    "description" => "ONLY include this if user specifies custom max hours for specific teachers (applies to ALL days for that teacher)",
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["teacher_id", "max_hours"],
                                        "properties" => [
                                            "teacher_id" => [
                                                "type" => "string",
                                                "minLength" => 1,
                                                "description" => "Unique identifier for the teacher"
                                            ],
                                            "max_hours" => [
                                                "type" => "number",
                                                "minimum" => 0,
                                                "maximum" => 24,
                                                "description" => "Maximum daily hours for this specific teacher across all days"
                                            ]
                                        ],
                                        "additionalProperties" => false
                                    ],
                                    "minItems" => 1,
                                    "uniqueItems" => true
                                ],
                                "teacher_day_exceptions" => [
                                    "type" => ["array", "null"],
                                    "description" => "ONLY include this if user specifies custom max hours for specific teachers on specific days (overrides both default and teacher_exceptions)",
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["teacher_id", "day", "max_hours"],
                                        "properties" => [
                                            "teacher_id" => [
                                                "type" => "string",
                                                "minLength" => 1,
                                                "description" => "Unique identifier for the teacher"
                                            ],
                                            "day" => [
                                                "type" => "string",
                                                "enum" => self::DAYS_OF_WEEK,
                                                "description" => "Specific day of the week for this exception"
                                            ],
                                            "max_hours" => [
                                                "type" => "number",
                                                "minimum" => 0,
                                                "maximum" => 8,
                                                "description" => "Maximum hours for this specific teacher on this specific day"
                                            ]
                                        ],
                                        "additionalProperties" => false
                                    ],
                                    "minItems" => 1
                                ]
                            ],
                            "additionalProperties" => false
                        ],
                        "teacher_max_weekly_hours" => [
                            "type" => "object",
                            "required" => ["max_hours"],
                            "properties" => [
                                "max_hours" => [
                                    "type" => "number",
                                    "minimum" => 0,
                                    "maximum" => 40,
                                    "description" => "Default maximum weekly hours that applies to all teachers"
                                ],
                                "teacher_exception" => [
                                    "type" => ["array", "null"],
                                    "description" => "ONLY include this if user specifies custom max weekly hours for specific teachers",
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["teacher_id", "max_hours"],
                                        "properties" => [
                                            "teacher_id" => [
                                                "type" => "string",
                                                "minLength" => 1,
                                                "description" => "Unique identifier for the teacher"
                                            ],
                                            "max_hours" => [
                                                "type" => "number",
                                                "minimum" => 0,
                                                "maximum" => 168,
                                                "description" => "Maximum weekly hours for this specific teacher"
                                            ]
                                        ],
                                        "additionalProperties" => false
                                    ],
                                    "minItems" => 1,
                                    "uniqueItems" => true
                                ]
                            ],
                            "additionalProperties" => false
                        ],
                        "schedule_max_periods_per_day" => [
                            "type" => "object",
                            "required" => ["max_periods"],
                            "properties" => [
                                "max_periods" => [
                                    "type" => "integer",
                                    "minimum" => 1,
                                    "maximum" => 20,
                                    "description" => "Default maximum number of periods per day that applies to all days"
                                ],
                                "day_exceptions" => [
                                    "type" => ["array", "null"],
                                    "description" => "ONLY include this if user specifies custom max periods for specific days of the week",
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["day", "max_periods"],
                                        "properties" => [
                                            "day" => [
                                                "type" => "string",
                                                "enum" => self::DAYS_OF_WEEK,
                                                "description" => "Specific day of the week"
                                            ],
                                            "max_periods" => [
                                                "type" => "integer",
                                                "minimum" => 1,
                                                "maximum" => 20,
                                                "description" => "Maximum number of periods for this specific day"
                                            ]
                                        ],
                                        "additionalProperties" => false
                                    ],
                                    "minItems" => 1
                                ]
                            ],
                            "additionalProperties" => false
                        ],
                        "schedule_max_free_periods_per_day" => [
                            "type" => "object",
                            "required" => ["max_free_periods"],
                            "properties" => [
                                "max_free_periods" => [
                                    "type" => "integer",
                                    "minimum" => 0,
                                    "maximum" => 10,
                                    "description" => "Default maximum number of free/gap periods allowed per day for all days. Use 0 to disallow any free periods."
                                ],
                                "day_exceptions" => [
                                    "type" => ["array", "null"],
                                    "description" => "ONLY include this if user specifies custom max free periods for specific days of the week",
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["day", "max_free_periods"],
                                        "properties" => [
                                            "day" => [
                                                "type" => "string",
                                                "enum" => self::DAYS_OF_WEEK,
                                                "description" => "Specific day of the week"
                                            ],
                                            "max_free_periods" => [
                                                "type" => "integer",
                                                "minimum" => 0,
                                                "maximum" => 10,
                                                "description" => "Maximum number of free/gap periods allowed for this specific day. Use 0 to require consecutive scheduling."
                                            ]
                                        ],
                                        "additionalProperties" => false
                                    ],
                                    "minItems" => 1
                                ]
                            ],
                            "additionalProperties" => false
                        ],
                        "course_max_daily_frequency" => [
                            "type" => "object",
                            "required" => ["max_frequency"],
                            "properties" => [
                                "max_frequency" => [
                                    "type" => "integer",
                                    "minimum" => 1,
                                    "maximum" => 10,
                                    "description" => "Default maximum number of times the same subject/course can appear per day for all courses on all days"
                                ],
                                "course_exceptions" => [
                                    "type" => ["array", "null"],
                                    "description" => "ONLY include this if user specifies custom max frequency for specific courses (applies to ALL days for that course)",
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["course_id", "max_frequency"],
                                        "properties" => [
                                            "course_id" => [
                                                "type" => "string",
                                                "minLength" => 1,
                                                "description" => "Unique identifier for the course/subject"
                                            ],
                                            "max_frequency" => [
                                                "type" => "integer",
                                                "minimum" => 1,
                                                "maximum" => 10,
                                                "description" => "Maximum number of times this specific course can appear per day across all days"
                                            ]
                                        ],
                                        "additionalProperties" => false
                                    ],
                                    "minItems" => 1,
                                    "uniqueItems" => true
                                ],
                                "course_day_exceptions" => [
                                    "type" => ["array", "null"],
                                    "description" => "ONLY include this if user specifies custom max frequency for specific courses on specific days (overrides both default and course_exceptions)",
                                    "items" => [
                                        "type" => "object",
                                        "required" => ["course_id", "day", "max_frequency"],
                                        "properties" => [
                                            "course_id" => [
                                                "type" => "string",
                                                "minLength" => 1,
                                                "description" => "Unique identifier for the course/subject"
                                            ],
                                            "day" => [
                                                "type" => "string",
                                                "enum" => self::DAYS_OF_WEEK,
                                                "description" => "Specific day of the week for this exception"
                                            ],
                                            "max_frequency" => [
                                                "type" => "integer",
                                                "minimum" => 1,
                                                "maximum" => 10,
                                                "description" => "Maximum number of times this specific course can appear on this specific day"
                                            ]
                                        ],
                                        "additionalProperties" => false
                                    ],
                                    "minItems" => 1
                                ]
                            ],
                            "additionalProperties" => false
                        ],
                        'course_requested_time_slots' => [
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
                        'requested_assignments' => [
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
                        ],
                        'hall_requested_time_windows' => [
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
                        ],
                        'teacher_requested_time_windows' => [
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
                        ],
                        'requested_free_periods' => [
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
                        ],
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
