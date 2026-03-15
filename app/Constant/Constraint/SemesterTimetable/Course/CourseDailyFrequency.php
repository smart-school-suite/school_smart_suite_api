<?php

namespace App\Constant\Constraint\SemesterTimetable\Course;

class CourseDailyFrequency
{
    public const KEY = "course_daily_frequency";
    public const TITLE = "Course Daily Frequency";
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Course\CourseDailyFrequency::class;
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Course\CourseDailyFrequencyInterpreter::class;
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Course\CourseDailyFrequencySuggestion::class;
    public const TYPE = "soft";
    public const DESCRIPTION = "Limits how many times the same course or subject can be scheduled on any single day. Applies to all courses by default, with optional exceptions for specific courses.";
    public const EXAMPLE = [
        [
            "max_frequency" => 2
        ],
        [
            "max_frequency" => 1
        ],
        [
            "max_frequency" => 3
        ],
        [
            "max_frequency" => 1,
            "course_exceptions" => [
                [
                    "course_id"     => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                    "max_frequency" => 3
                ],
                [
                    "course_id"     => "abcdef12-3456-7890-abcd-ef1234567890",
                    "max_frequency" => 2
                ]
            ]
        ],
        [
            "max_frequency" => 2,
            "course_exceptions" => [
                [
                    "course_id"     => "123e4567-e89b-12d3-a456-426614174000",
                    "max_frequency" => 4
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
