<?php

namespace App\Constant\Violation\ExamTimetable\Assignment;

class RequestedAssignment
{
    public const KEY = "requested_assignment_violation";
    public const TITLE = "Requested Assignment Violation";
    // public const HANDLER = \App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment::class;
    // public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Assignment\RequestedAssignmentViolation::class;
    // public const VIOLATION_SUGGESTION_HANDLER = \App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Assignment\RequestedAssignmentViolationSuggestion::class;
    public const CATEGORY = "assignment_violation";

    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            // 'handler' => self::HANDLER,
            // 'violation_handler' => self::VIOLATION_HANDLER,
            // 'description' => self::DESCRIPTION,
            // 'violation_suggestion_handler' => self::VIOLATION_SUGGESTION_HANDLER,
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
