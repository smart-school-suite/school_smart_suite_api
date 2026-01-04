<?php

namespace App\Constant\Authorization\PermissionDefinations\Teacher;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\TeacherPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Teacher\TeacherSpecialtyPermissions;

class TeacherSpecialtyPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherSpecialtyPermissions::ASSIGN,
                "Assign",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherSpecialtyPermissions::REMOVE,
                "Remove",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherSpecialtyPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherSpecialtyPermissions::VIEW_ASSIGNED,
                "View Assigned",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherSpecialtyPermissions::VIEW_UNASSIGNED,
                "View Unassigned",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_SPECIALTY_MANAGER,
                Guards::TEACHER,
                TeacherSpecialtyPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
