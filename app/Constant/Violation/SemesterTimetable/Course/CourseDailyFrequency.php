<?php

namespace App\Constant\Violation\SemesterTimetable\Course;

class CourseDailyFrequency
{
    public const KEY = "course_daily_frequency_violation";
    public const TITLE = "Course Daily Frequency Violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Course\CourseDailyFrequency::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\CourseDailyFrequencyViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Course\CourseDailyFrequencyViolationSuggestion::class;
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
            'violation_suggestion_handler' => self::VIOLATION_SUGGESTION_HANDLER
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
