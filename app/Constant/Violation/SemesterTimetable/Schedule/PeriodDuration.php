<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class PeriodDuration
{
    public const KEY = "schedule_period_duration_minutes_violation";
    public const TITLE = "Period Duration Minutes  Violation";
    public const CATEGORY = "schedule_violation";
    public const DESCRIPTION = "This violation occurs when a scheduled period's duration does not match the defined constraints for that period. The period duration constraint ensures that each scheduled period adheres to specific minimum and maximum duration requirements, which can be set based on institutional policies or course requirements. Violating this constraint indicates that the timetable has assigned a period with a duration that is either too short or too long compared to the defined limits, which can lead to scheduling inefficiencies and conflicts for students, teachers, and administrators.";
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
