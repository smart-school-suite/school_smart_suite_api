<?php

namespace App\Constant\Authorization\RoleDefination;

use App\Constant\Authorization\Builder\RoleBuilder;
use App\Constant\Authorization\Roles\SchoolAdminRoles;
use App\Constant\System\Guards;

class SchoolAdminRoleDef
{
    public static function all(): array
    {
        return [
            RoleBuilder::make(
                SchoolAdminRoles::SCHOOL_ADMIN,
                "School Admin",
                "Grants standard administrative access to manage daily school operations, including student records, staff assignments, and academic scheduling.",
                Guards::SCHOOL_ADMIN
            ),
            RoleBuilder::make(
                SchoolAdminRoles::SCHOOL_SUPER_ADMIN,
                "School Super Admin",
                "The highest authority level. Provides unrestricted access to all system modules, financial data, security settings, and the ability to manage other administrator accounts.",
                Guards::SCHOOL_ADMIN
            )
        ];
    }
}
