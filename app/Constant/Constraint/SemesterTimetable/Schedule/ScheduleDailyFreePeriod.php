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
            'suggestion_handler' => self::SUGGESTION_HANDLER
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
