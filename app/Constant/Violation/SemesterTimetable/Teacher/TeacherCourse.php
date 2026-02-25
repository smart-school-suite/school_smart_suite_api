<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherCourse
{
    public const KEY = "teacher_course";
    public const TITLE = "Teacher Course Violation";

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
