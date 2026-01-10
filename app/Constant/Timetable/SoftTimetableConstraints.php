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
                "teacher_minimum_break_between_classes" => 15,
                "teacher_even_subject_distribution" => true,
                "teacher_balanced_workload" => true,
                "teacher_avoid_split_double_periods" => true,
                "course_load_proportionality" => true,
                "course_avoid_clustering" => true,
                "course_minimum_gap_between_sessions" => true,
                "course_room_suitability" => [
                    "theory" => "lecture",
                    "practical" => "lab"
                ],
                "course_preferred_time_of_day" => [
                    "theory" => "morning",
                    "practical" => "afternoon"
                ],
                "course_credit_hour_density_control" => true,
                "course_spread_across_week" => true,
                "hall_type_suitability" => [
                    "theory" => "lecture",
                    "practical" => "lab"
                ],
                "hall_change_minimization" => true,
                "hall_usage_balance" => true,
                "time_max_periods_per_day" => 8,
                "time_min_free_periods_per_day" => 1,
                "time_balanced_daily_workload" => true,
                "time_balanced_weekly_workload" => true,
                "time_avoid_consecutive_heavy_subjects" => true,
                "time_consecutive_period_allowance" => [
                    "practicals" => true,
                    "theory" => true
                ],
                "time_min_gap_between_sessions" => 1.0,
                "time_subject_frequency_per_day" => 2
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
                        'course_fixed_days' => [
                            'type' => 'array',
                            'description' => 'List of courses that must be scheduled on specific days and/or times.',
                            'items' => [
                                'type' => 'object',
                                'required' => ['course_id', 'course_name'],
                                'properties' => [
                                    'course_id' => [
                                        'type' => 'string',
                                        'description' => 'Unique identifier for the course.'
                                    ],
                                    'course_name' => [
                                        'type' => 'string',
                                        'description' => 'Human-readable name of the course.'
                                    ],
                                    'fixed_slots' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'required' => ['day'],
                                            'properties' => [
                                                'day' => [
                                                    'type' => 'string',
                                                    'enum' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
                                                ],
                                                'time' => [
                                                    'type' => 'string',
                                                    'description' => 'Specific time range (e.g., 07:00 - 10:00). If omitted, the whole day is allowed.',
                                                    'example' => '07:00 - 10:00'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'teacher_max_daily_hours' => [
                            'type' => 'number',
                            'minimum' => 1,
                            'maximum' => 12,
                            'description' => 'Maximum teaching hours per day for a teacher (e.g., 6.0–8.0). Use lower values for more relaxed schedules.',
                            'example' => 8.0
                        ],
                        'teacher_max_weekly_hours' => [
                            'type' => 'number',
                            'minimum' => 10,
                            'maximum' => 60,
                            'description' => 'Maximum teaching hours per week (e.g., 30.0–40.0). Lower = lighter workload.',
                            'example' => 40.0
                        ],
                        'teacher_minimum_break_between_classes' => [
                            'type' => 'integer',
                            'minimum' => 5,
                            'maximum' => 60,
                            'description' => 'Minimum break in minutes between two classes for the same teacher (e.g., 10, 15, 30).',
                            'example' => 15
                        ],
                        'teacher_even_subject_distribution' => [
                            'type' => 'boolean',
                            'description' => 'true = spread a teacher\'s subjects evenly across the week; false = no preference.',
                            'example' => true
                        ],
                        'teacher_balanced_workload' => [
                            'type' => 'boolean',
                            'description' => 'true = distribute teaching load evenly across days; false = allow uneven days.',
                            'example' => true
                        ],
                        'teacher_avoid_split_double_periods' => [
                            'type' => 'boolean',
                            'description' => 'true = prevent splitting double periods (e.g., across lunch); false = allow.',
                            'example' => true
                        ],
                        'course_load_proportionality' => [
                            'type' => 'boolean',
                            'description' => 'true = heavier courses get more weekly slots proportionally.',
                            'example' => true
                        ],
                        'course_avoid_clustering' => [
                            'type' => 'boolean',
                            'description' => 'true = avoid too many sessions of the same course on the same day/week.',
                            'example' => true
                        ],
                        'course_minimum_gap_between_sessions' => [
                            'type' => 'boolean',
                            'description' => 'true = enforce at least one day gap between sessions of the same course.',
                            'example' => true
                        ],
                        'course_room_suitability' => [
                            'type' => 'object',
                            'properties' => [
                                'theory' => [
                                    'type' => 'string',
                                    'enum' => ['lecture'],
                                    'description' => 'Preferred room type for theory classes.',
                                    'example' => 'lecture'
                                ],
                                'practical' => [
                                    'type' => 'string',
                                    'enum' => ['lab'],
                                    'description' => 'Preferred room type for practical/lab classes.',
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
                        'course_credit_hour_density_control' => [
                            'type' => 'boolean',
                            'description' => 'true = spread high-credit courses to avoid overload on certain days.',
                            'example' => true
                        ],
                        'course_spread_across_week' => [
                            'type' => 'boolean',
                            'description' => 'true = distribute course sessions evenly across the week.',
                            'example' => true
                        ],
                        'hall_type_suitability' => [
                            'type' => 'object',
                            'properties' => [
                                'theory' => [
                                    'type' => 'string',
                                    'enum' => ['lecture', 'any'],
                                    'description' => 'Suitable hall type for theory.',
                                    'example' => 'lecture'
                                ],
                                'practical' => [
                                    'type' => 'string',
                                    'enum' => ['lab', 'any'],
                                    'description' => 'Suitable hall type for practicals.',
                                    'example' => 'lab'
                                ]
                            ],
                            'required' => ['theory', 'practical'],
                            'additionalProperties' => false
                        ],
                        'hall_change_minimization' => [
                            'type' => 'boolean',
                            'description' => 'true = minimize room/hall changes for the same class/course.',
                            'example' => true
                        ],
                        'hall_usage_balance' => [
                            'type' => 'boolean',
                            'description' => 'true = balance usage across all available halls.',
                            'example' => true
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
                        'time_balanced_daily_workload' => [
                            'type' => 'boolean',
                            'description' => 'true = keep daily workload similar across the week.',
                            'example' => true
                        ],
                        'time_balanced_weekly_workload' => [
                            'type' => 'boolean',
                            'description' => 'true = balance total weekly load.',
                            'example' => true
                        ],
                        'time_avoid_consecutive_heavy_subjects' => [
                            'type' => 'boolean',
                            'description' => 'true = avoid back-to-back difficult or long subjects.',
                            'example' => true
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
                        'time_min_gap_between_sessions' => [
                            'type' => 'number',
                            'minimum' => 0.5,
                            'maximum' => 48.0,
                            'description' => 'Minimum gap in hours between two sessions of the same subject (e.g., 1.0, 24.0).',
                            'example' => 1.0
                        ],
                        'time_subject_frequency_per_day' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'maximum' => 5,
                            'description' => 'Maximum times a subject can appear in one day (e.g., 1 or 2).',
                            'example' => 2
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
