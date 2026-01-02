<?php

namespace App\Constant\Authorization\Permissions\SchoolEvent;

class SchoolEventTagPermissions
{
    public const  CREATE =  "school_event_tag.create";
    public const  UPDATE = "school_event_tag.update";
    public const DELETE = "school_event_tag.delete";
    public const VIEW = "school_event_tag.view";
    public const DEACTIVATE = "school_event_tag.deactivate";
    public const ACTIVATE = "school_event_tag.activate";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::VIEW,
            self::DEACTIVATE,
            self::ACTIVATE
        ];
    }
}
