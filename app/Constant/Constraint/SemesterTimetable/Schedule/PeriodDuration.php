<?php

namespace App\Constant\Constraint\SemesterTimetable\Schedule;

class PeriodDuration
{
    public const KEY = "schedule_period_duration_minutes";
    public const TITLE = "Schedule Period Duration Minutes";
    public const DESCRIPTION = "Sets the standard length (in minutes) of each class period or timetable slot. All periods must follow this duration every day unless specific day exceptions are defined.";
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration::class;
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Schedule\PeriodDurationInterpreter::class;
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule\PeriodDurationSuggestion::class;
    public const TYPE = "hard";
    public const CATEGORY = "schedule_constraint";
    public const VIOLATION = [
        "operational_period_violation",
    ];
    public const EXAMPLE = [
        [
            "duration_minutes" => 60
        ],
        [
            "duration_minutes" => 45
        ],
        [
            "duration_minutes" => 90,
            "day_exceptions" => [
                [
                    "day"              => "tuesday",
                    "duration_minutes" => 120
                ]
            ]
        ],
        [
            "duration_minutes" => 45,
            "day_exceptions" => [
                [
                    "day"              => "monday",
                    "duration_minutes" => 60
                ],
                [
                    "day"              => "friday",
                    "duration_minutes" => 30
                ]
            ]
        ],
        [
            "duration_minutes" => 50,
            "day_exceptions" => [
                [
                    "day"              => "thursday",
                    "duration_minutes" => 100
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
