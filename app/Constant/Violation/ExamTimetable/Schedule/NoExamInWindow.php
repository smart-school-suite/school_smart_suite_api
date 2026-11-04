<?php

namespace App\Constant\Violation\ExamTimetable\Schedule;

class NoExamInWindow
{
    public const KEY = "no_exam_in_window";
    public const TITLE = "No Exam In Window";
    public const CATEGORY = "schedule_violation";
    public const DESCRIPTION = "No exam session was scheduled during the requested time, so the invigilator or hall request for that time could not be used.";

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
