<?php

namespace App\Constant\Violation\SemesterTimetable\Hall;

class HallBusy
{
    public const KEY = "hall_busy";
    public const TITLE = "Hall Busy";
    public const CATEGORY = "hall_violation";
    public const DESCRIPTION = "This violation occurs when a hall is scheduled for more than one course at the same time, indicating that the hall is double-booked. The hall busy constraint ensures that each hall can only be assigned to one course during any given time slot, and violating this constraint indicates that the timetable has assigned multiple courses to the same hall simultaneously, which can lead to scheduling conflicts and logistical issues for students, teachers, and administrators.";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Hall\HallBusy::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Hall\HallBusy::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Hall\HallBusySuggestion::class;
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
