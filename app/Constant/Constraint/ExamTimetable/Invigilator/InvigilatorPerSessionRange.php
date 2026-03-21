<?php

namespace App\Constant\Constraint\ExamTimetable\Invigilator;

class InvigilatorPerSessionRange
{
    public const KEY = "invigilator_per_session_range";
    public const TITLE = "Ivigilator per session range";
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Course\CourseDailyFrequency::class;
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Course\CourseDailyFrequencyInterpreter::class;
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Course\CourseDailyFrequencySuggestion::class;
    public const TYPE = "soft";
    public const DESCRIPTION = "Ensures that certain courses are scheduled together in the same periods across different department.";
    public const EXAMPLE = [
        [
            "date" => "2026-10-15",
            "start_time" => "10:00",
            "end_time" => "11:00",
            "course_id" => "123e4567-e89b-12d3-a456-426614174000"
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
