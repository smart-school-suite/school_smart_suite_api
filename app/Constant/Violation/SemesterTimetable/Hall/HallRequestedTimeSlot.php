<?php

namespace App\Constant\Violation\SemesterTimetable\Hall;

class HallRequestedTimeSlot
{
    public const KEY = "hall_requested_time_slot_violation";
    public const TITLE = "Hall Requested Time Slot Violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Hall\HallRequestedTimeSlot::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Hall\HallRequestedTimeSlotViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Hall\HallRequestedTimeSlotViolationSuggestion::class;
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
