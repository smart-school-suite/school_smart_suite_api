<?php

namespace App\Constant\Violation\SemesterTimetable\Hall;

class HallBusy
{
    public const KEY = "hall_busy";
    public const TITLE = "Hall Busy";

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
