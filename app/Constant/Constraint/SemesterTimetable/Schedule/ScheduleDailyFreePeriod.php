<?php

namespace App\Constant\Constraint\SemesterTimetable\Schedule;

class ScheduleDailyFreePeriod
{
    public const KEY = "schedule_free_periods_per_day";
    public const TITLE = "Schedule Free Periods Per Day";
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyFreePeriod::class;
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Schedule\ScheduleDailyFreePeriodInterpreter::class;
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule\DailyFreePeriodSuggestion::class;
    public const TYPE = "soft";
    public const DESCRIPTION = "Sets the maximum number of unscheduled (free/gap) periods allowed per day in the student timetable. Applies to all days by default, with optional exceptions for specific days.";
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
            "max_free_periods" => 2
        ],
        [
            "max_free_periods" => 1
        ],
        [
            "max_free_periods" => 0
        ],
        [
            "max_free_periods" => 1,
            "day_exceptions" => [
                [
                    "day"              => "monday",
                    "max_free_periods" => 3
                ],
                [
                    "day"              => "friday",
                    "max_free_periods" => 0
                ]
            ]
        ],
        [
            "max_free_periods" => 3,
            "day_exceptions" => [
                [
                    "day"              => "wednesday",
                    "max_free_periods" => 1
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
            'type' => self::TYPE,
            'description' => self::DESCRIPTION,
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
