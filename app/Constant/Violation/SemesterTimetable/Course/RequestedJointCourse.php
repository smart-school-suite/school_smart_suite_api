<?php

namespace App\Constant\Violation\SemesterTimetable\Course;

class RequestedJointCourse
{
    public const KEY = "joint_course_period_violation";
    public const TITLE = "Requested Joint Course Period Violation";
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
