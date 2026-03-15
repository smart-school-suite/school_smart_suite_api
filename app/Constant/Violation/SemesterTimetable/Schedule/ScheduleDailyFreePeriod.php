<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class ScheduleDailyFreePeriod
{
    public const KEY = "schedule_free_periods_per_day_violation";
    public const TITLE = "Schedule Daily Free periods violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyFreePeriod::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\ScheduleDailyFreePeriodViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Schedule\DailyFreePeriodViolationSuggestion::class;
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
            'violation_suggestion_handle' => self::VIOLATION_SUGGESTION_HANDLER
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
