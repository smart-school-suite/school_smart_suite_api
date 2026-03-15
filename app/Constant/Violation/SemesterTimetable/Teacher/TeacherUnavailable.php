<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherUnavailable
{
    public const KEY = "teacher_unavailable";
    public const TITLE = "Teacher Unavailable";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherUnavailable::class;
    public static function toArray(): array {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
        ];
    }

    public static function title(): string {
        return self::TITLE;
    }
    public static function key(): string {
        return self::KEY;
    }
}
