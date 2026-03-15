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
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'description' => self::DESCRIPTION,
            'type' => self::TYPE,
            'interpreter_handler' => self::INTERPRETER_HANDLER,
            'suggestion_handler' => self::SUGGESTION_HANDLER
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
