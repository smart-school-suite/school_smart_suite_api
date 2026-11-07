<?php

namespace App\Constant\Violation\ExamTimetable\Schedule;

class NoExamSession
{
    public const KEY = "no_exam_session";
    public const TITLE = "No Exam At That Time";
    public const CATEGORY = "schedule_violation";
    public const DESCRIPTION = "No exam of this timetable takes place during the requested time, so the hall or invigilator request has nothing to attach to.";

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
