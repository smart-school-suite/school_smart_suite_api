<?php

namespace App\Constant\Violation\SemesterTimetable\Schedule;

class BreakPeriod
{
   public const KEY = "break_period_violation";
   public const TITLE = "Break Period Violation";

    public static function toArray(): array {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
        ];
    }

    public static function title(): string {
        return self::TITLE;
    }

    public static function key(): string {
        return self::KEY;
    }
}
