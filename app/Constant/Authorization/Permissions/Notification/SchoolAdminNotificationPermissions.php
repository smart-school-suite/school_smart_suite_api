<?php

namespace App\Constant\Authorization\Permissions\Notification;

class SchoolAdminNotificationPermissions
{
    public const VIEW = "school_admin_notification.view";
    public const DELETE = "school_admin_notification.delete";
    public const READ = "school_admin_notification.read";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::DELETE,
            self::READ
        ];
    }
}
