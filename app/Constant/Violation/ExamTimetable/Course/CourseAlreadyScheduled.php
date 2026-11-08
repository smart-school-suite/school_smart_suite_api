<?php

namespace App\Constant\Violation\ExamTimetable\Course;

class CourseAlreadyScheduled
{
    public const KEY = "course_already_scheduled";
    public const TITLE = "Course Already Scheduled";
    public const CATEGORY = "course_violation";
    public const DESCRIPTION = "The course already has an exam session from an earlier request. Each course is examined once.";

    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'category' => self::CATEGORY,
            'description' => self::DESCRIPTION,
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
