<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class MaxDailyPeriod
{
    public const KEY = "max_daily_period_violation";
    public const TITLE = "Max Daily Period Violation";

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
