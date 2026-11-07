<?php

namespace App\Constant\Constraint\ExamTimetable\Assignment;

class RequestedAssignment
{
    public const KEY = "requested_assignment";
    public const TITLE = "Requested Assignment";
    public const DESCRIPTION = "Asks for a course's exam to take place at a specific date and time, in a given hall and with given invigilators.";
    public const TYPE = "soft";
    public const CATEGORY = "schedule_constraint";
    public const BLOCKERS = ["operational_period"];
    public const EXAMPLE = [
        [

            "course_id" => "123e4567-e89b-12d3-a456-426614174000",
            "date" => "2026-10-15",
            "start_time" => "10:00",
            "end_time" =>  "11:00",
            "hall_ids" => ["2b1e4567-e89b-12d3-a456-426614174000"],
            "invigilator_ids" => ["7c9e4567-e89b-12d3-a456-426614174000"]

        ]
    ];

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
