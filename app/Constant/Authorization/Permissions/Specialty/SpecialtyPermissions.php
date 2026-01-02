<?php

namespace App\Constant\Authorization\Permissions\Specialty;

class SpecialtyPermissions
{
    public const CREATE = "specialty.create";
    public const DELETE = "specialty.delete";
    public const VIEW = "specialty.view";
    public const UPDATE = "specialty.update";
    public const DEACTIVATE = "specialty.deactivate";
    public const ACTIVATE = "specialty.activate";

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
