<?php

namespace App\Constant\Constraint\ExamTimetable\Course;

class CoursePlacement
{
    public const KEY = "course_placement";
    public const TITLE = "Every Course Gets An Exam";
    public const TYPE = "hard";
    public const CATEGORY = "course_constraint";
    public const DESCRIPTION = "Every course of the exam must get one exam session with a time, enough hall seats for all candidates and invigilators who do not teach the course. A course that cannot be placed makes the timetable incomplete.";

    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'type' => self::TYPE,
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
