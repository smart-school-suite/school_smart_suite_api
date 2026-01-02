<?php

namespace App\Constant\Authorization\Permissions\Department;

class DepartmentPermissions
{
    public const CREATE = "department.create";
    public const DELETE = "department.delete";
    public const VIEW = "department.view";
    public const UPDATE = "department.update";
    public const DEACTIVATE = "department.deactivate";
    public const ACTIVATE = "department.activate";

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
