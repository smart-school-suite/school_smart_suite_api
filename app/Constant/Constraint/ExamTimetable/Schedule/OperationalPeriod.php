<?php

namespace App\Constant\Constraint\ExamTimetable\Schedule;

class OperationalPeriod
{
    public const KEY = "operational_period";
    public const TITLE = "Operational Period";
    public const DESCRIPTION = "Defines the daily opening-to-closing hours of the institution. No classes, exams, activities or any scheduling is allowed outside these hours on any day (unless exceptions are specified).";
    public const TYPE = "hard";
    public const CATEGORY = "schedule_constraint";
    public const BLOCKERS = [];

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
                    "date"        => "2026-10-15",
                    "start_time" => "08:00",
                    "end_time"   => "16:00"
                ]
            ]
        ],
    ];

    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            //'handler' => self::HANDLER,
            'type' => self::TYPE,
            'description' => self::DESCRIPTION,
            // 'interpreter_handler' => self::INTERPRETER_HANDLER,
            // 'suggestion_handler' => self::SUGGESTION_HANDLER
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
