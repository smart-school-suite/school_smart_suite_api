<?php

namespace App\Constant\Authorization\Permissions\Gender;

class GenderPermissions
{
    public const CREATE = "gender.create";
    public const UPDATE = "gender.update";
    public const VIEW = "gender.view";
    public const DELETE = "gender.delete";
    public const ACTIVATE  = "gender.activate";
    public const DEACTIVATE =  "gender.deactivate";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::UPDATE,
            self::VIEW,
            self::DELETE,
            self::ACTIVATE,
            self::DEACTIVATE
        ];
    }
}
