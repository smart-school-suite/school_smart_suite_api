<?php

namespace App\Constant\Authorization\Permissions\RBAC;

class RBACPermissions
{
    public const CREATE = "permission.create";
    public const DELETE = "permission.delete";
    public const VIEW = "permission.view";
    public const UPDATE = "permission.update";
    public const DEACTIVATE = "permission.deactivate";
    public const ACTIVATE = "permission.activate";

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
