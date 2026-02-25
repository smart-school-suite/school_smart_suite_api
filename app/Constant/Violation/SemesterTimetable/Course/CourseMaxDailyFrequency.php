<?php

namespace App\Constant\Violation\SemesterTimetable\Course;

class CourseMaxDailyFrequency
{
    public const KEY = "max_course_daily_frequency_violation";
    public const TITLE = "Course Max Daily Frequency Violation";

    public static function toArray(): array {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
        ];
    }

    public static function title(): string {
        return self::TITLE;
    }

    public static function key(): string {
        return self::KEY;
    }
}
