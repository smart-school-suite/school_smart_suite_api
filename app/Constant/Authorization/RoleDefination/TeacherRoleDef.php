<?php

namespace App\Constant\Authorization\RoleDefination;

use App\Constant\Authorization\Builder\RoleBuilder;
use App\Constant\Authorization\Roles\TeacherRoles;
use App\Constant\System\Guards;
use App\Models\Teacher;

class TeacherRoleDef
{
    public static function all(): array
    {
        return [
            RoleBuilder::make(
                TeacherRoles::TEACHER,
                "Teacher",
                "Provides access to the faculty portal for managing classroom attendance, recording student marks, viewing teaching schedules, and communicating with assigned student groups.",
                Guards::TEACHER
            ),
        ];
    }
}
