<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class ScheduleDailyFreePeriod
{
    public const KEY = "schedule_free_periods_per_day_violation";
    public const TITLE = "Schedule Daily Free periods violation";
    public const CATEGORY = "schedule_violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyFreePeriod::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\ScheduleDailyFreePeriodViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Schedule\DailyFreePeriodViolationSuggestion::class;
    public const DESCRIPTION = "This violation occurs when the schedule does not provide the required number of free periods per day. The daily free period constraint ensures that students and teachers have adequate breaks throughout the day, and violating this constraint indicates that the timetable has scheduled too many classes without sufficient free periods.";
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
