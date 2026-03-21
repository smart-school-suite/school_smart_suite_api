<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class BreakPeriod
{
    public const KEY = "break_period_violation";
    public const TITLE = "Break Period Violation";
    public const CATEGORY = "schedule_violation";
    public const DESCRIPTION = "This violation occurs when a break period is not scheduled according to the defined constraints. The break period constraint ensures that there are designated breaks between classes for students and teachers to rest and transition between courses. Violating this constraint indicates that the timetable has scheduled classes back-to-back without the required breaks, which can lead to fatigue and reduced productivity for both students and teachers.";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\BreakPeriodViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Schedule\BreakPeriodViolationSuggestion::class;
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
