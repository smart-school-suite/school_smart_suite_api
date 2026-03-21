<?php

namespace App\Constant\Constraint\SemesterTimetable\Teacher;

class TeacherRequestedTimeSlot
{
    public const KEY = "teacher_requested_time_windows";
    public const TITLE = "Teacher Requested Time Windows";
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Teacher\TeacherRequestedTimeWindowInterpreter::class;
    public const SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Teacher\TeacherRequestedTimeWindowSuggestion::class;
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot::class;
    public const DESCRIPTION = "Specifies preferred days and/or time windows for scheduling classes taught by specific teachers. These are soft preferences — the scheduler should try to respect them when possible, but can assign teaching sessions outside these windows if needed due to other constraints.";
    public const TYPE = "soft";
    public const CATEGORY = "teacher_constraint";
    public const VIOLATION = [
        "course_daily_frequency_violation",
        "course_requested_time_slot_violation",
        "joint_course_period_violation",
        "hall_busy",
        "hall_requested_time_slot_violation",
        "break_period_violation",
        "operational_period_violation",
        "schedule_period_duration_minutes_violation",
        "schedule_periods_per_day_violation",
        "teacher_busy",
        "teacher_daily_hours_violation",
        "teacher_requested_time_slot_violation",
        "teacher_unavailable",
        "teacher_weekly_hours_violation"
    ];
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'description' => self::DESCRIPTION,
            'type' => self::TYPE,
            'interpreter_handler' => self::INTERPRETER_HANDLER,
            'suggestion_handler' => self::SUGGESTION_HANDLER,
            'category' => self::CATEGORY
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
