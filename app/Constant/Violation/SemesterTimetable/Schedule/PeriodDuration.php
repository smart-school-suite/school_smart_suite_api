<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class PeriodDuration
{
    public const KEY = "schedule_period_duration_minutes_violation";
    public const TITLE = "Period Duration Minutes  Violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\PeriodDurationViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Schedule\PeriodDurationViolationSuggestion::class;
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
