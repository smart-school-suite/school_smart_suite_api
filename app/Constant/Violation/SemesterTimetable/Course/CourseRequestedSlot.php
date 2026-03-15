<?php

namespace App\Constant\Violation\SemesterTimetable\Course;

class CourseRequestedSlot
{
    public const KEY = "course_requested_time_slot_violation";
    public const TITLE = "Course Requested Slot Violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\CourseRequestedTimeSlotViolation::class;
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_suggestion_handler' => self::VIOLATION_SUGGESTION_HANDLER,
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
