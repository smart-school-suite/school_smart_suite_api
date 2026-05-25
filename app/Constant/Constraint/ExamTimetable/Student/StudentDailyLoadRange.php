<?php

namespace App\Constant\Constraint\ExamTimetable\Student;

class StudentDailyLoadRange
{
    public const KEY = "student_daily_load_range";
    public const TITLE = "Student Daily Load Range";
    public const DESCRIPTION = "Sets the minimum and maximum number of exams a student can be scheduled for within a single day to prevent academic overload and ensure fair testing conditions.";
    public const TYPE = "soft";
    public const CATEGORY = "schedule_constraint";
    public const BLOCKERS = [""];

    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            //'handler' => self::HANDLER,
            'type' => self::TYPE,
            'description' => self::DESCRIPTION,
            // 'interpreter_handler' => self::INTERPRETER_HANDLER,
            // 'suggestion_handler' => self::SUGGESTION_HANDLER
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
