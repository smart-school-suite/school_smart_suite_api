<?php

namespace App\Constant\Violation\SemesterTimetable\Teacher;

class TeacherWeeklyHours
{
    public const KEY = "teacher_weekly_hours_violation";
    public const TITLE = "Teacher Weekly Hour Violation";
    public const CATEGORY = "teacher_violation";
    public const DESCRIPTION = "This violation occurs when a teacher is scheduled to teach more hours in a week than the allowed maximum. The teacher weekly hours constraint ensures that teachers have a manageable workload each week, and violating this constraint indicates that the timetable has assigned a teacher to teach for more hours than is considered reasonable or permissible in a single week, which can lead to fatigue and decreased effectiveness for the teacher.";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Teacher\TeacherWeeklyHours::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Teacher\TeacherWeeklyHourViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Teacher\TeacherWeeklyHourViolationSuggestion::class;
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
            'violation_suggestion_handler' => self::VIOLATION_SUGGESTION_HANDLER,
            'category' => self::CATEGORY,
            'description' => self::DESCRIPTION
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
