<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherInsufficiency
{
    public const KEY = "teacher_insufficiency";
    public const TITLE = "Teacher Insufficiency";

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
