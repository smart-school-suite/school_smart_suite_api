<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherRequestedTimeSlot
{
    public const KEY = "teacher_requested_time_slot_violation";
    public const TITLE = "Teacher Requested Time Slot Violation";
    public const CATEGORY = "teacher_violation";
    public const DESCRIPTION = "This violation occurs when a teacher is scheduled in a specific time slot that was explicitly requested by the teacher to be avoided. The teacher requested time slot constraint allows teachers to specify particular time slots during which they prefer not to have classes scheduled, and violating this constraint indicates that the timetable has assigned a teacher to one of these undesired time slots.";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherRequestedTimeSlotViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Teacher\TeacherRequestedTimeSlotViolation::class;
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
