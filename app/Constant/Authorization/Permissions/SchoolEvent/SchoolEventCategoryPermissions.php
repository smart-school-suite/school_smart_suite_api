<?php

namespace App\Constant\Authorization\Permissions\SchoolEvent;

class SchoolEventCategoryPermissions
{
    public const CREATE = "school_event_category.create";
    public const VIEW  = "school_event_category.view";
    public const UPDATE = "school_event_category.update";
    public const DELETE = "school_event_category.delete";
    public const ACTIVATE = "school_event_category.activate";
    public const DEACTIVATE = "school_event_category.deactivate";

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
