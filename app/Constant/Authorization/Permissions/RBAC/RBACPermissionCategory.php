<?php

namespace App\Constant\Authorization\Permissions\RBAC;

class RBACPermissionCategory
{
    public const CREATE = "permission_category.create";
    public const DELETE = "permission_category.delete";
    public const VIEW = "permission_category.view";
    public const UPDATE = "permission_category.update";
    public const DEACTIVATE = "permission_category.deactivate";
    public const ACTIVATE = "permission_category.activate";

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
