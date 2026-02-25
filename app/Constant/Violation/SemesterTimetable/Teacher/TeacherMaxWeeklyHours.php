<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherMaxWeeklyHours
{
   public const KEY = "max_teacher_weekly_hour_violation";
   public const TITLE = "Teacher Max Weekly Hour Violation";

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
