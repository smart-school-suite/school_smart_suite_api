<?php

namespace App\Constant\Constraint\SemesterTimetable\Schedule;

class OperationalPeriod
{
    public const KEY = "operational_period";
    public const TITLE = "Operational Period";
    public const TYPE = "Hard";
    public const DESCRIPTION = "Defines the daily opening-to-closing hours of the institution. No classes, exams, activities or any scheduling is allowed outside these hours on any day (unless exceptions are specified).";
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule\OperationalPeriodSuggestion::class;
    public const EXAMPLE = [
        [
            "start_time" => "07:00",
            "end_time"   => "18:00"
        ],
        [
            "start_time" => "08:00",
            "end_time"   => "17:00",
            "day_exceptions" => [
                [
                    "day"        => "wednesday",
                    "start_time" => "08:00",
                    "end_time"   => "16:00"
                ]
            ]
        ],
        [
            "start_time" => "07:30",
            "end_time"   => "16:30",
            "day_exceptions" => [
                [
                    "day"        => "friday",
                    "start_time" => "07:30",
                    "end_time"   => "14:00"
                ],
                [
                    "day"        => "saturday",
                    "start_time" => "08:00",
                    "end_time"   => "13:00"
                ]
            ]
        ],
        [
            "start_time" => "08:00",
            "end_time"   => "15:00",
            "day_exceptions" => [
                [
                    "day"        => "monday",
                    "start_time" => "07:30",
                    "end_time"   => "16:30"
                ]
            ]
        ],
        [
            "start_time" => "07:00",
            "end_time"   => "19:00",
            "day_exceptions" => [
                [
                    "day"        => "saturday",
                    "start_time" => "08:00",
                    "end_time"   => "14:00"
                ]
            ]
        ]
    ];
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod::class;
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'type' => self::TYPE,
            'description' => self::DESCRIPTION,
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
