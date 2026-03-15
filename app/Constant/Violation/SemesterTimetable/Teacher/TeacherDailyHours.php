<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherDailyHours
{
    public const KEY = "teacher_daily_hours_violation";
    public const TITLE = "Teacher Daily Hour Violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Teacher\TeacherDailyHours::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherDailyHourViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Teacher\TeacherDailyHourViolationSuggestion::class;
    public static function toArray(): array {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
            'violation_suggestion_handler' => self::VIOLATION_SUGGESTION_HANDLER
        ];
    }

    public static function title(): string {
        return self::TITLE;
    }

    public static function key(): string {
        return self::KEY;
    }
}
