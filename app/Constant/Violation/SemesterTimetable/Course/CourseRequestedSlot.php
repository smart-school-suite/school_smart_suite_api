<?php

namespace App\Constant\Violation\SemesterTimetable\Course;

class CourseRequestedSlot
{
    public const KEY = "course_requested_time_slot_violation";
    public const TITLE = "Course Requested Slot Violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\CourseRequestedTimeSlotViolation::class;
    public const CATEGORY = "course_violation";
    public const DESCRIPTION = "This violation occurs when a course is scheduled in a specific time slot that was explicitly requested by a teacher, student group, or department to be avoided. The course requested slot constraint allows stakeholders to specify particular time slots during which they prefer not to have certain courses scheduled, and violating this constraint indicates that the timetable has assigned a course to one of these undesired time slots.";
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
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
