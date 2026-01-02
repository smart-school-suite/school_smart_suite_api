<?php

namespace App\Constant\Authorization\Permissions\Semester;

class SchoolSemesterPermissions
{
    public const CREATE = "school_semester.create";
    public const DELETE = "school_semester.delete";
    public const UPDATE = "school_semester.update";
    public const VIEW = "school_semester.view";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW
        ];
    }
}
