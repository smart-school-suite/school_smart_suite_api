<?php

namespace App\Constant\Constraint\SemesterTimetable\Schedule;

class ScheduleDailyPeriod
{
    public const KEY = "schedule_periods_per_day";
    public const TITLE = "Schedule Periods Per Day";
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyPeriod::class;
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Schedule\ScheduleDailyPeriodInterpreter::class;
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule\DailyPeriodSuggestion::class;
    public const DESCRIPTION = "Sets the maximum number of class periods or sessions that can be scheduled on any single day across the entire timetable. Applies to all days by default, with optional exceptions for specific days.";
    public const TYPE = "soft";
    public const CATEGORY = "schedule_constraint";
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
            "max_periods" => 6
        ],
        [
            "max_periods" => 7
        ],
        [
            "max_periods" => 5,
            "day_exceptions" => [
                [
                    "day"         => "monday",
                    "max_periods" => 7
                ],
                [
                    "day"         => "friday",
                    "max_periods" => 4
                ]
            ]
        ],
        [
            "max_periods" => 8,
            "day_exceptions" => [
                [
                    "day"         => "saturday",
                    "max_periods" => 4
                ]
            ]
        ],
        [
            "max_periods" => 4
        ]
    ];

    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'type' => self::TYPE,
            'description' => self::DESCRIPTION,
            'interpreter_handler' => self::INTERPRETER_HANDLER,
            'suggestion_handler' => self::SUGGESTION_HANDLER,
            'category' => self::CATEGORY,
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
