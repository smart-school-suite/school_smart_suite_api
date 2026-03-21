<?php

namespace App\Constant\Constraint\SemesterTimetable\Schedule;

class BreakPeriod
{
    public const KEY = "break_period";
    public const TITLE = "Break Period";
    public const TYPE = "Hard";
    public const DESCRIPTION = "Fixed daily break or lunch time during which no classes, exams, sessions or activities can be scheduled. Applies every operational day unless exceptions are specified.";
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Schedule\BreakPeriodInterpreter::class;
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule\BreakPeriodSuggestion::class;
    public const CATEGORY = "schedule_constraint";
    public const VIOLATION = [
        "operational_period_violation",
    ];
    public const EXAMPLE = [
        [
            "start_time" => "12:00",
            "end_time"   => "13:00"
        ],
        [
            "start_time" => "12:00",
            "end_time"   => "13:00",
            "no_break_exceptions" => ["monday", "wednesday"]
        ],
        [
            "start_time"     => "12:00",
            "end_time"       => "13:00",
            "day_exceptions" => [
                [
                    "day"        => "friday",
                    "start_time" => "14:00",
                    "end_time"   => "15:00"
                ]
            ]
        ],
        [
            "start_time"          => "12:30",
            "end_time"            => "13:30",
            "no_break_exceptions" => ["saturday"],
            "day_exceptions"      => [
                [
                    "day"        => "friday",
                    "start_time" => "14:00",
                    "end_time"   => "14:45"
                ]
            ]
        ],
        [
            "start_time" => "10:00",
            "end_time"   => "10:30",
            "no_break_exceptions" => ["sunday", "saturday"]
        ]
    ];
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod::class;
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
