<?php

namespace App\Constant\Authorization\PermissionDefinations\Teacher;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\TeacherPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Teacher\TeacherPermissions;

class TeacherPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherPermissions::ACTIVATE,
                "Activate",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_MANAGER,
                Guards::TEACHER,
                TeacherPermissions::AVATAR_DELETE,
                "Avatar Delete",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_MANAGER,
                Guards::TEACHER,
                TeacherPermissions::AVATAR_UPLOAD,
                "Avatar Upload",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_MANAGER,
                Guards::TEACHER,
                TeacherPermissions::PROFILE_UPDATE,
                "Profile Update",
                ""
            ),
        ];
    }
}
