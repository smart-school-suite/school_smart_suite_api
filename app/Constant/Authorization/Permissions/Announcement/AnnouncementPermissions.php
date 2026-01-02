<?php

namespace App\Constant\Authorization\Permissions\Announcement;

class AnnouncementPermissions
{
    public const CREATE = "announcement.create";
    public const UPDATE = "announcement.update";
    public const VIEW = "announcement.view";
    public const DELETE = "announcement.delete";

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
