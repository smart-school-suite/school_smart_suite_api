<?php

namespace App\Constant\Authorization\Permissions\Timetable;

class TimetablePermissions
{
    public const CREATE = "timetable.create";
    public const UPDATE = "timetable.update";
    public const DELETE = "timetable.delete";
    public const VIEW = "timetable.view";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::VIEW
        ];
    }
}
