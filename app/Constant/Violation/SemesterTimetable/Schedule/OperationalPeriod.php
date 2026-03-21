<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class OperationalPeriod
{
    public const KEY = "operational_period_violation";
    public const TITLE = "Operational Period Violation";
    public const CATEGORY = "schedule_violation";
    public const DESCRIPTION = "This violation occurs when a course is scheduled outside of the defined operational hours of the institution. The operational period constraint ensures that classes are only scheduled during specific hours of the day when the institution is open and operational. Violating this constraint indicates that the timetable has assigned a course to a time slot that falls outside of these designated operational hours, which can lead to scheduling conflicts and logistical issues for students, teachers, and administrators.";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Schedule\OperationalPeriodViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Schedule\OperationalPeriodViolationSuggestion::class;
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
