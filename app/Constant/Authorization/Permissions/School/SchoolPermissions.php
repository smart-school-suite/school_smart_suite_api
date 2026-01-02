<?php

namespace App\Constant\Authorization\Permissions\School;

class SchoolPermissions
{
    public const VIEW = "school.view";
    public const UPDATE = "school.update";
    public const DELETE = "school.delete";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::UPDATE,
            self::DELETE
        ];
    }
}
