<?php

namespace App\Constant\Violation\ExamTimetable\Schedule;

class SessionDuration
{
    public const KEY = "session_duration_violation";
    public const TITLE = "Session Duration Violation";
    // public const HANDLER = \App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse::class;
    // public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\RequiredJointCourseViolation::class;
    public const CATEGORY = "hall_violation";
    public const DESCRIPTION = "The time does not match the exam duration set for the course.";
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
