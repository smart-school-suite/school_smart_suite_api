<?php

namespace App\Constant\Constraint\SemesterTimetable\Teacher;

class TeacherWeeklyHours
{
    public const KEY = "teacher_weekly_hours";
    public const TITLE = "Teacher Weekly Hours";
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Teacher\TeacherWeeklyHours::class;
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Teacher\TeacherWeeklyHourInterpreter::class;
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Teacher\TeacherWeeklyHourSuggestion::class;
    public const DESCRIPTION = "Sets the maximum total number of teaching hours any teacher can be assigned across the entire week. Applies to all teachers by default, with optional exceptions for specific teachers.";
    public const TYPE = "soft";
    public const CATEGORY = "teacher_constraint";
    public const VIOLATION = [
        "course_daily_frequency_violation",
        "course_requested_time_slot_violation",
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
        "teacher_unavailable",
        "teacher_weekly_hours_violation"
    ];
    public const EXAMPLE = [
        [
            "max_hours" => 30
        ],
        [
            "max_hours" => 24
        ],
        [
            "max_hours" => 28,
            "teacher_exceptions" => [
                [
                    "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                    "max_hours"  => 25
                ]
            ]
        ],
        [
            "max_hours" => 32,
            "teacher_exceptions" => [
                [
                    "teacher_id" => "123e4567-e89b-12d3-a456-426614174000",
                    "max_hours"  => 35
                ],
                [
                    "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                    "max_hours"  => 28
                ]
            ]
        ],
        [
            "max_hours" => 20
        ]
    ];
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'description' => self::DESCRIPTION,
            'type' => self::TYPE,
            'interpreter_handler' => self::INTERPRETER_HANDLER,
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
