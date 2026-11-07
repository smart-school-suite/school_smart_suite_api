<?php

namespace App\Constant\Violation\ExamTimetable\Invigilator;

class InvigilatorBusy
{
    public const KEY = "invigilator_busy";
    public const TITLE = "Invigilator Busy";
    // public const HANDLER = \App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse::class;
    // public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\RequiredJointCourseViolation::class;
    public const CATEGORY = "hall_violation";
    public const DESCRIPTION = "The invigilator already invigilates another exam session at this time.";
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            // 'handler' => self::HANDLER,
            // 'violation_handler' => self::VIOLATION_HANDLER,
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
