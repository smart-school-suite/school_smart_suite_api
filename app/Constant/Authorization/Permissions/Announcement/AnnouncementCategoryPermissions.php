<?php

namespace App\Constant\Authorization\Permissions\Announcement;

class AnnouncementCategoryPermissions
{
    public const CREATE = "announcement_category.create";
    public const VIEW  = "announcement_category.view";
    public const UPDATE = "announcement_category.update";
    public const DELETE = "announcement_category.delete";
    public const ACTIVATE = "announcement_category.activate";
    public const DEACTIVATE = "announcement_category.deactivate";

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
