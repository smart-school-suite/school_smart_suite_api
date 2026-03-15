<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherWeeklyHours
{
    public const KEY = "teacher_weekly_hours_violation";
    public const TITLE = "Teacher Weekly Hour Violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Teacher\TeacherWeeklyHours::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherWeeklyHourViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Teacher\TeacherWeeklyHourViolationSuggestion::class;
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
