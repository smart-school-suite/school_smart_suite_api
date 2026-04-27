<?php

namespace App\Constant\Constraint\ExamTimetable\Schedule;

class SessionDuration
{
    public const KEY = "session_duration";
    public const TITLE = "Session Duration";
    public const DESCRIPTION = "Specifies break periods during which no exams can be scheduled. This can include lunch breaks, recesses, or any other designated free periods.";
    public const TYPE = "hard";
    public const CATEGORY = "schedule_constraint";
    public const BLOCKERS = ["operational_period"];
    public const EXAMPLES = [
        [
            "duration" => 60,
            "course_exceptions" => [
                [
                    "course_id" => "01d0ad4a-20f4-4472-8f9e-d76f656220a7",
                    "duration" =>  120
                ]
            ]
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
