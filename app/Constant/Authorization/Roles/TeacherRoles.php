<?php

namespace App\Constant\Authorization\Roles;

class TeacherRoles
{
    public const TEACHER = "teacher";
    public static function all(): array
    {
        return [
            self::TEACHER
        ];
    }
}
