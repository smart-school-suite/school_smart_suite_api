<?php

namespace App\Constant\Violation\SemesterTimetable\Course;

class RequiredJointCourse
{
    public const KEY = "joint_course_period_violation";
    public const TITLE = "Requested Joint Course Period Violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\RequiredJointCourseViolation::class;
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
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
