<?php

namespace App\Constant\Authorization\Permissions\SchoolSetting;

class ExamSettingPermissions
{
    public const VIEW = "exam_setting.view";
    public const UPDATE = "exam_setting.update";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::UPDATE
        ];
    }
}
