<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherBusy
{
    public const KEY = "teacher_busy";
    public const TITLE = "Teacher Busy";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherBusy::class;
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
