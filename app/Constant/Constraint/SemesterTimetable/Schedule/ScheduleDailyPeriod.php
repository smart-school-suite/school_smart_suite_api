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
