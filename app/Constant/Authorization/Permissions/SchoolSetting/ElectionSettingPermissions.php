<?php

namespace App\Constant\Authorization\Permissions\SchoolSetting;

class ElectionSettingPermissions
{
    public const VIEW = "election_setting.view";
    public const UPDATE = "election_setting.update";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::UPDATE
        ];
    }
}
