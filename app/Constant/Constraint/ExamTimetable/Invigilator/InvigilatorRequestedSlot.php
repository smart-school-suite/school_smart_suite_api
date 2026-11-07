<?php

namespace App\Constant\Constraint\ExamTimetable\Invigilator;

class InvigilatorRequestedSlot
{
    public const KEY = "invigilator_requested_slot";
    public const TITLE = "Invigilator Requested Slot";
    public const DESCRIPTION = "Asks for an invigilator to invigilate the exam that takes place at a given date and time. It is honoured only when the invigilator is free and does not teach the course.";
    public const TYPE = "soft";
    public const CATEGORY = "schedule_constraint";
    public const BLOCKERS = ["operational_period"];

    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            // 'handler' => self::HANDLER,
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
