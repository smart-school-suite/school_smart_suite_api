<?php

namespace App\Constant\Violation\SemesterTimetable\Assignment;

class RequestedAssigment
{
    public const KEY = "requested_assignment_violation";
    public const TITLE = "Requested Assignment Violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Assignment\RequestedAssignmentViolation::class;
    public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Assignment\RequestedAssignmentViolationSuggestion::class;
    public const CATEGORY = "assignment_violation";
    public const DESCRIPTION = "This violation occurs when a course is scheduled in a time slot that was explicitly requested by a teacher, student group, or department to be avoided. The requested assignment constraint allows stakeholders to specify time slots during which they prefer not to have classes scheduled, and violating this constraint indicates that the timetable has assigned a course to one of these undesired time slots.";
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
            'description' => self::DESCRIPTION,
            'violation_suggestion_handler' => self::VIOLATION_SUGGESTION_HANDLER,
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
