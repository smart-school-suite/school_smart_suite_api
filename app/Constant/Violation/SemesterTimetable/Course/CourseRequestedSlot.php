<?php

namespace App\Constant\Violation\SemesterTimetable\Course;

class CourseRequestedSlot
{
    public const KEY = "course_requested_time_slot_violation";
    public const TITLE = "Course Requested Slot Violation";

    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
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
