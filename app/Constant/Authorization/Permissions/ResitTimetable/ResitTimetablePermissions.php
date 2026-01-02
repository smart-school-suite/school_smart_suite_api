<?php

namespace App\Constant\Authorization\Permissions\ResitTimetable;

class ResitTimetablePermissions
{
    public const CREATE = "resit_timetable.create";
    public const UPDATE = "resit_timetable.update";
    public const DELETE = "resit_timetable.delete";
    public const VIEW = "resit_timetable.view";

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
