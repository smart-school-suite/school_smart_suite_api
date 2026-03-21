<?php

namespace App\Constant\Constraint\ExamTimetable\Course;

class CourseTimeRequest
{
    public const KEY = "course_time_requests";
    public const TITLE = "Course Time Request";
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Course\CourseDailyFrequency::class;
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Course\CourseDailyFrequencyInterpreter::class;
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Course\CourseDailyFrequencySuggestion::class;
    public const TYPE = "soft";
    public const DESCRIPTION = "Allows courses to request specific time slots for their exams. This constraint can be used to accommodate special needs, avoid conflicts with other courses, or align with students' preferences.";
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
