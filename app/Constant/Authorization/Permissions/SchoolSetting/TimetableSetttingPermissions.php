<?php

namespace App\Constant\Authorization\Permissions\SchoolSetting;

class TimetableSetttingPermissions
{
    public const VIEW = "timetable_setting.view";
    public const UPDATE = "timetable_setting.update";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::UPDATE
        ];
    }
}
