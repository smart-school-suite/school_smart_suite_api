<?php

namespace App\Constant\Authorization\Permissions\SchoolSetting;

class PromotionSettingPermissions
{
    public const VIEW = "promotion_setting.view";
    public const UPDATE = "promotion_setting.update";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::UPDATE
        ];
    }
}
