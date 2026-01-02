<?php

namespace App\Constant\Authorization\Permissions\Announcement;

class AnnouncementTagPermissions
{

    public const  CREATE =  "announcement_tag.create";
    public const  UPDATE = "announcement_tag.update";
    public const DELETE = "announcement_tag.delete";
    public const VIEW = "announcement_tag.view";
    public const DEACTIVATE = "announcement_tag.deactivate";
    public const ACTIVATE = "announcement_tag.activate";

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
