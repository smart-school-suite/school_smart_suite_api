<?php

namespace App\Constant\Constraint\ExamTimetable\Course;

class RequiredJointCourseSession
{
    public const KEY = "required_joint_course_periods";
    public const TITLE = "Required Joint Course Periods";
    // public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Course\CourseDailyFrequencyInterpreter::class;
    // public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Course\CourseDailyFrequencySuggestion::class;
    public const TYPE = "hard";
    public const DESCRIPTION = "Fixes the date and time of an exam shared with other specialties. The exam is placed exactly there, so the timetable fails if that time is not usable.";
    public const EXAMPLE = [
        [
            "date" => "2026-10-15",
            "start_time" => "10:00",
            "end_time" => "11:00",
            "course_id" => "123e4567-e89b-12d3-a456-426614174000"
        ]
    ];
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
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
