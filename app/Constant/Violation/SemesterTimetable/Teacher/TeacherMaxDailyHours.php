<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherMaxDailyHours
{
    public const KEY = "max_teacher_daily_hour_violation";
    public const TITLE = "Teacher Max Daily Hour Violation";

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
