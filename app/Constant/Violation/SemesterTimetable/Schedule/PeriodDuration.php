<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class PeriodDuration
{
    public const KEY = "period_duration_violation";
    public const TITLE = "Period Duration Violation";

    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
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
