<?php

namespace App\Constant\Constraint\SemesterTimetable\Teacher;

class TeacherDailyHours
{
    public const KEY = "teacher_daily_hours";
    public const TITLE = "Teacher Daily Hours";
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Teacher\TeacherDailyHours::class;
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Teacher\TeacherDailyHourInterpreter::class;
        public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Teacher\TeacherDailyHourSuggestion::class;
    public const TYPE = "soft";
    public const DESCRIPTION = "Sets the maximum number of teaching hours any teacher can be assigned on a single day. Applies to all teachers by default, with optional exceptions for specific teachers.";
    public const EXAMPLE = [
        [
            "max_hours" => 6
        ],
        [
            "max_hours" => 7
        ],
        [
            "max_hours" => 5,
            "teacher_exceptions" => [
                [
                    "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                    "max_hours"  => 8
                ]
            ]
        ],
        [
            "max_hours" => 6,
            "teacher_exceptions" => [
                [
                    "teacher_id" => "123e4567-e89b-12d3-a456-426614174000",
                    "max_hours"  => 4
                ],
                [
                    "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                    "max_hours"  => 7
                ]
            ]
        ],
        [
            "max_hours" => 8
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
