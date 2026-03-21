<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherUnavailable
{
    public const KEY = "teacher_unavailable";
    public const TITLE = "Teacher Unavailable";
    public const CATEGORY = "teacher_violation";
    public const DESCRIPTION = "This violation occurs when a teacher is scheduled to teach a course during a time slot when they are unavailable. The teacher unavailable constraint allows teachers to specify time slots during which they cannot teach due to other commitments or personal reasons, and violating this constraint indicates that the timetable has assigned a teacher to a course during one of these unavailable time slots, which can lead to scheduling conflicts and logistical issues for students, teachers, and administrators.";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherUnavailable::class;
    public static function toArray(): array {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
            'category' => self::CATEGORY,
            'description' => self::DESCRIPTION
        ];
    }

    public static function title(): string {
        return self::TITLE;
    }
    public static function key(): string {
        return self::KEY;
    }
}
