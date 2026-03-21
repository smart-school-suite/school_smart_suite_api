<?php

namespace App\Constant\Violation\SemesterTimetable\Hall;

class HallRequestedTimeSlot
{
    public const KEY = "hall_requested_time_slot_violation";
    public const TITLE = "Hall Requested Time Slot Violation";
    public const CATEGORY = "hall_violation";
    public const DESCRIPTION = "This violation occurs when a hall is scheduled in a specific time slot that was explicitly requested by a teacher, student group, or department to be avoided. The hall requested time slot constraint allows stakeholders to specify particular time slots during which they prefer not to have certain halls scheduled, and violating this constraint indicates that the timetable has assigned a hall to one of these undesired time slots.";
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
