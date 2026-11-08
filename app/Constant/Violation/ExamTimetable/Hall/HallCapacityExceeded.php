<?php

namespace App\Constant\Violation\ExamTimetable\Hall;

class HallCapacityExceeded
{
    public const KEY = "hall_capacity_exceeded";
    public const TITLE = "Hall Capacity Exceeded";
    // public const HANDLER = \App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse::class;
    // public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\RequiredJointCourseViolation::class;
    public const CATEGORY = "hall_violation";
    public const DESCRIPTION = "The halls chosen do not have enough seats for every candidate.";
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
