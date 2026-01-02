<?php

namespace App\Constant\Authorization\Permissions\Level;

class LevelPermissions
{
    public const CREATE = "level.create";
    public const DELETE = "level.delete";
    public const UPDATE = "level.update";
    public const VIEW = "level.view";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW
        ];
    }
}
