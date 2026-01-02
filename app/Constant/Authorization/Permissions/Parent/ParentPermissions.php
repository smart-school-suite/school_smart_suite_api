<?php

namespace App\Constant\Authorization\Permissions\Parent;

class ParentPermissions
{
    public const CREATE = "parent.create";
    public const UPDATE = "parent.update";
    public const DELETE = "parent.delete";
    public const VIEW = "parent.view";

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
