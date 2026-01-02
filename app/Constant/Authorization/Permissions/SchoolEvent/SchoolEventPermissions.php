<?php

namespace App\Constant\Authorization\Permissions\SchoolEvent;

class SchoolEventPermissions
{
    public const CREATE = "school_event.create";
    public const UPDATE = "school_event.update";
    public const VIEW = "school_event.view";
    public const DELETE = "school_event.delete";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::UPDATE,
            self::VIEW,
            self::DELETE
        ];
    }
}
