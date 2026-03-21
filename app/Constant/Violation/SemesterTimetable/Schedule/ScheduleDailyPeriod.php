<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class ScheduleDailyPeriod
{
    public const KEY = "schedule_periods_per_day_violation";
    public const TITLE = "Schedule Daily Periods Violation";
    public const CATEGORY = "schedule_violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyPeriod::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\ScheduleDailyPeriodViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Schedule\DailyPeriodViolationSuggestion::class;
    public const DESCRIPTION = "This violation occurs when the schedule does not adhere to the defined constraints for the number of periods scheduled per day. The daily period constraint ensures that there is an appropriate number of periods scheduled each day, and violating this constraint indicates that the timetable has either scheduled too many or too few periods in a single day, which can lead to scheduling imbalances and conflicts for students, teachers, and administrators.";
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
