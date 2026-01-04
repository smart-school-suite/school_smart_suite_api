<?php

namespace App\Constant\Authorization\Permissions\RBAC;

class RBACPermissionRoles
{
    public const CREATE = "role.create";
    public const DELETE = "role.delete";
    public const VIEW = "role.view";
    public const UPDATE = "role.update";
    public const DEACTIVATE = "role.deactivate";
    public const ACTIVATE = "role.activate";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW,
            self::DEACTIVATE,
            self::ACTIVATE
        ];
    }
}
