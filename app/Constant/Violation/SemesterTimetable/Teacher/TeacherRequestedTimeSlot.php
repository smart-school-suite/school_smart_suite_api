<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherRequestedTimeSlot
{
    public const KEY = "teacher_requested_time_slot_violation";
    public const TITLE = "Teacher Requested Time Slot Violation";

    public static function toArray(): array {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
        ];
    }

    public static function title(): string {
        return self::TITLE;
    }

    public static function key(): string {
        return self::KEY;
    }
}
