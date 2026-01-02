<?php

namespace App\Constant\Authorization\Permissions\Notification;

class StudentNotificationPermissions
{
    public const VIEW = "student_notification.view";
    public const DELETE = "student_notification.delete";
    public const READ = "student_notification.read";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::DELETE,
            self::READ
        ];
    }
}
