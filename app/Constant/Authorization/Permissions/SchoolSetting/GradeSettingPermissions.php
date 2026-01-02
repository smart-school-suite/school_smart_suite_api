<?php

namespace App\Constant\Authorization\Permissions\SchoolSetting;

class GradeSettingPermissions
{
    public const VIEW = "grade_setting.view";
    public const UPDATE = "grade_setting.update";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::UPDATE
        ];
    }
}
