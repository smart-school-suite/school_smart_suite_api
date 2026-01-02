<?php

namespace App\Constant\Authorization\Permissions\SchoolSetting;

class ResitSettingPermissions
{
    public const VIEW = "resit_setting.view";
    public const UPDATE = "resit_setting.update";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::UPDATE
        ];
    }
}
