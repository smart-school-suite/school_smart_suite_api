<?php

namespace App\Constant\Violation\SemesterTimetable\Course;

class CourseDailyFrequency
{
    public const KEY = "course_daily_frequency_violation";
    public const TITLE = "Course Daily Frequency Violation";
    public const CATEGORY = "course_violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Course\CourseDailyFrequency::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\CourseDailyFrequencyViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Course\CourseDailyFrequencyViolationSuggestion::class;
    public const DESCRIPTION = "This violation occurs when a course is scheduled more than once in a single day, exceeding the allowed daily frequency for that course. The course daily frequency constraint is designed to prevent scheduling a course multiple times on the same day, which can lead to scheduling conflicts and an unbalanced timetable. Violating this constraint indicates that the timetable has assigned a course to multiple time slots within the same day, which may not be desirable for students, teachers, or the institution.";
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
            'violation_suggestion_handler' => self::VIOLATION_SUGGESTION_HANDLER,
            'category' => self::CATEGORY,
            'description' => self::DESCRIPTION
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
