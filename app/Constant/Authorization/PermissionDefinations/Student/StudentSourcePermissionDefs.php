<?php

namespace App\Constant\Authorization\PermissionDefinations\Student;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Student\StudentSourcePermissions;

class StudentSourcePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_SOURCE_MANAGER,
                Guards::APP_ADMIN,
                StudentSourcePermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_SOURCE_MANAGER,
                Guards::APP_ADMIN,
                StudentSourcePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_SOURCE_MANAGER,
                Guards::APP_ADMIN,
                StudentSourcePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_SOURCE_MANAGER,
                Guards::APP_ADMIN,
                StudentSourcePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_SOURCE_MANAGER,
                Guards::APP_ADMIN,
                StudentSourcePermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_SOURCE_MANAGER,
                Guards::APP_ADMIN,
                StudentSourcePermissions::ACTIVATE,
                "Activate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_SOURCE_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentSourcePermissions::VIEW,
                "View",
                ""
            )
        ];
    }
}
