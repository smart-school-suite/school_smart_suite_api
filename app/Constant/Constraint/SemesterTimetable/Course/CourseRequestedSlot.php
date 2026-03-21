<?php

namespace App\Constant\Constraint\SemesterTimetable\Course;

class CourseRequestedSlot
{
    public const KEY = "course_requested_time_slots";
    public const TITLE = "Course Requested Time Slot";
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot::class;
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Course\CourseRequestedTimeSlotInterpreter::class;
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Course\CourseRequestedTimeSlotSuggestion::class;
    public const DESCRIPTION = "Specifies preferred days and/or time windows for scheduling particular courses. These are wishes the scheduler should try to respect when possible, but can override if needed due to other constraints.";
    public const TYPE = "soft";
    public const CATEGORY = "course_constraint";
    public const VIOLATION = [
        "course_daily_frequency_violation",
        "joint_course_period_violation",
        "hall_busy",
        "hall_requested_time_slot_violation",
        "break_period_violation",
        "operational_period_violation",
        "schedule_period_duration_minutes_violation",
        "schedule_periods_per_day_violation",
        "teacher_busy",
        "teacher_daily_hours_violation",
        "teacher_requested_time_slot_violation",
        "teacher_unavailable"
    ];
    public const EXAMPLE = [
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
                    ["day" => "wednesday"]
                ]
            ]
        ],
        [
            [
                "course_id" => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                "slots" => [
                    [
                        "start_time" => "08:00",
                        "end_time"   => "10:30"
                    ]
                ]
            ]
        ],
        [
            [
                "course_id" => "math101",
                "slots" => [
                    ["day" => "tuesday"],
                    ["day" => "thursday"]
                ]
            ],
            [
                "course_id" => "physics202",
                "slots" => [
                    [
                        "start_time" => "09:00",
                        "end_time"   => "10:45"
                    ]
                ]
            ]
        ],
        [
            [
                "course_id" => "english301",
                "slots" => [
                    ["day" => "friday"]
                ]
            ]
        ]
    ];
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'interpreter_handler' => self::INTERPRETER_HANDLER,
            'description' => self::DESCRIPTION,
            'type' => self::TYPE,
            'suggestion_handler' => self::SUGGESTION_HANDLER,
            'category' => self::CATEGORY
        ];
    }

    public static function title(): string
    {
        return self::TITLE;
    }

    public static function key(): string
    {
        return self::KEY;
    }
}
