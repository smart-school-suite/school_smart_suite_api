<?php

namespace App\Constant\Authorization\Permissions\Hall;

class HallPermissions
{
    public const CREATE = "hall.create";
    public const UPDATE = "hall.update";
    public const DELETE = "hall.delete";
    public const VIEW = "hall.view";
    public const ASSIGN_HALL = "hall.assign_exam_hall";
    public const DEACTIVATE_HALL = "hall.deactivate";
    public const ACTIVATE_HALL = "hall.activate";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::VIEW,
            self::ASSIGN_HALL,
            self::DEACTIVATE_HALL,
            self::ACTIVATE_HALL
        ];
    }
}
