<?php

namespace App\Constant\Constraint\ExamTimetable\Invigilator;

class InvigilatorRequestedSlot
{
    public const KEY = "invigilator_requested_slot";
    public const TITLE = "Invigilator Requested Slot";
    public const DESCRIPTION = "Forces an invigilator to be assigned to a specific exam session at a set date and time, ensuring their availability is locked for that period regardless of standard conflict resolution.";
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
