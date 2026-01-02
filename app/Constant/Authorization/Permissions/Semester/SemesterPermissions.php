<?php

namespace App\Constant\Authorization\Permissions\Semester;

class SemesterPermissions
{
    public const CREATE = "semester.create";
    public const DELETE = "semester.delete";
    public const UPDATE = "semester.update";
    public const VIEW = "semester.view";
    public const ACTIVATE = "semester.activate";
    public const DEACTIVATE = "semester.deactivate";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW,
            self::ACTIVATE,
            self::DEACTIVATE
        ];
    }
}
