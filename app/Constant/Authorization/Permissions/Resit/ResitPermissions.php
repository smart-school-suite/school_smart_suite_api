<?php

namespace App\Constant\Authorization\Permissions\Resit;

class ResitPermissions
{
    public const VIEW = "resit.view";
    public const DELETE = "resit.delete";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::DELETE
        ];
    }
}
