<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherBusy
{
    public const KEY = "teacher_busy";
    public const TITLE = "Teacher Busy";
    public const CATEGORY = "teacher_violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherBusy::class;
    public const DESCRIPTION = "This violation occurs when a teacher is scheduled to teach more than one course at the same time, indicating that the teacher is double-booked. The teacher busy constraint ensures that each teacher can only be assigned to one course during any given time slot, and violating this constraint indicates that the timetable has assigned multiple courses to the same teacher simultaneously, which can lead to scheduling conflicts and logistical issues for students, teachers, and administrators.";
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
            'category' => self::CATEGORY,
            'description' => self::DESCRIPTION
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
