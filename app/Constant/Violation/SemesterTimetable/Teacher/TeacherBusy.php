<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherBusy
{
    public const KEY = "teacher_busy";
    public const TITLE = "Teacher Busy";

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
