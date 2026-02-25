<?php

namespace App\Constant\Violation\SemesterTimetable\Assignment;

class RequestedAssigment
{
    public const KEY = "requested_assignment_violation";
    public const TITLE = "Requested Assignment Violation";

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
