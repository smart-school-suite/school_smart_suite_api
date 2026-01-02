<?php

namespace App\Constant\Authorization\Permissions\Announcement;

class AnnouncementLabelPermissions
{
    public const CREATE =  "announcement_label.create";
    public const UPDATE = "announcement_label.update";
    public const DELETE = "announcement_label.delete";
    public const VIEW  = "announcement_label.view";
    public const ACTIVATE = "announcement_label.activate";
    public const DEACTIVATE = "announcement_label.deactivate";

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
