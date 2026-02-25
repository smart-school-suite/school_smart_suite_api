<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherUnavailable
{
    public const KEY = "teacher_unavailable";
    public const TITLE = "Teacher Unavailable";

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
