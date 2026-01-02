<?php

namespace App\Constant\Authorization\Permissions\Student;

class StudentSourcePermissions
{
    public const CREATE = "student_source.create";
    public const DELETE = "student_source.delete";
    public const UPDATE = "student_source.update";
    public const VIEW = "student_source.view";
    public const ACTIVATE = "student_source.activate";
    public const DEACTIVATE = "student_source.deactivate";

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
