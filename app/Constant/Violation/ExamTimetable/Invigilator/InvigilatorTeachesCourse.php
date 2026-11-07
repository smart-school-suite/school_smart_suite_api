<?php

namespace App\Constant\Violation\ExamTimetable\Invigilator;

class InvigilatorTeachesCourse
{
    public const KEY = "invigilator_teaches_course";
    public const TITLE = "Invigilator Teaches The Course";
    public const CATEGORY = "invigilator_violation";
    public const DESCRIPTION = "The invigilator teaches the course being examined. To prevent exam malpractice, nobody invigilates an exam for a course they teach.";

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
