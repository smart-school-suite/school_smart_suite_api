<?php

namespace App\Constant\Authorization\PermissionDefinations\Student;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\StudentPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Student\StudentPermissions;

class StudentPermissionDefs
{
    public static function all(): array
    {
        return  [
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentPermissions::ACTIVATE,
                "Activate",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                Guards::STUDENT,
                StudentPermissions::AVATAR_DELETE,
                "Avatar Delete",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                Guards::STUDENT,
                StudentPermissions::AVATAR_UPLOAD,
                "Avatar Upload",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                Guards::STUDENT,
                StudentPermissions::CHANGE_PASSWORD,
                "Change Password",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                Guards::STUDENT,
                StudentPermissions::PROFILE_UPDATE,
                "Profile Update",
                ""
            ),
        ];
    }
}
