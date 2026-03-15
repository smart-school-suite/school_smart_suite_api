<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class BreakPeriod
{
    public const KEY = "break_period_violation";
    public const TITLE = "Break Period Violation";
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
