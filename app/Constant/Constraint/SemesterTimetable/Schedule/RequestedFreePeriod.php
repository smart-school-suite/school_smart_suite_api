<?php

namespace App\Constant\Constraint\SemesterTimetable\Schedule;

class RequestedFreePeriod
{
    public const KEY = "requested_free_period";
    public const TITLE = "Requested Free Period";
    public const DESCRIPTION = "Specifies preferred days and/or time windows where the user would like free periods (gaps or empty slots) to appear in the student timetable. These are soft preferences — the scheduler should try to create these free slots when possible, but may fill or adjust them if needed due to other constraints.";
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod::class;
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Schedule\RequestedFreePeriodInterpreter::class;
        public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule\RequestedFreePeriodSuggestion::class;
    public const TYPE = "soft";
    public const EXAMPLE = [
        [
            [
                "day"        => "monday",
                "start_time" => "10:00",
                "end_time"   => "11:00"
            ],
            [
                "day"        => "friday",
                "start_time" => "10:00",
                "end_time"   => "11:00"
            ]
        ],
        [
            [
                "day" => "monday"
            ],
            [
                "day" => "friday"
            ]
        ],
        [
            [
                "start_time" => "12:00",
                "end_time"   => "13:00"
            ]
        ],
        [
            [
                "day"        => "wednesday"
            ],
            [
                "day"        => "thursday",
                "start_time" => "11:30",
                "end_time"   => "12:30"
            ],
            [
                "start_time" => "14:00",
                "end_time"   => "15:00"
            ]
        ],
        [
            [
                "day" => "tuesday"
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
