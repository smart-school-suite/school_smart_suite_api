<?php

namespace App\Constant\Authorization\Permissions\Hall;

class SpecialtyHallPermissions
{
    public const ASSIGN = "specialty_hall.assign";
    public const REMOVE = "specialty_hall.remove";
    public const VIEW_ASSIGNED = "specialty_hall.view_assigned";
    public const VIEW_UNASSIGNED = "specialty_hall.view_unassigned";
    public const VIEW = "specialty_hall.view";
    public static function all(): array
    {
        return [
            self::ASSIGN,
            self::REMOVE,
            self::VIEW_ASSIGNED,
            self::VIEW_UNASSIGNED,
            self::VIEW
        ];
    }
}
