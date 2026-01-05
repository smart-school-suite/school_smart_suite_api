<?php

namespace App\Constant\Authorization\RoleDefination;

use App\Constant\Authorization\Builder\RoleBuilder;
use App\Constant\Authorization\Roles\StudentRoles;
use App\Constant\System\Guards;

class StudentRoleDef
{
    public static function all(): array
    {
        return [
            RoleBuilder::make(
                StudentRoles::STUDENT,
                "Student",
                "Grants personal access to the student portal to view timetables, track academic progress, register for exams, and receive institutional announcements.",
                Guards::STUDENT
            ),
        ];
    }
}
