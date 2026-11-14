<?php

namespace App\Constant\Violation\ExamTimetable\Student;

class CandidateBusy
{
    public const KEY = "candidate_busy_violation";
    public const TITLE = "Candidates Sit Another Exam";
    public const CATEGORY = "student_violation";
    public const DESCRIPTION = "Some candidates of this exam sit another exam at this time (another active exam or resit timetable).";

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
