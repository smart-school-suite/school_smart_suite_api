<?php

namespace App\Constant\Authorization\PermissionDefinations\Semester;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Semester\SemesterPermissions;

class SemesterPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::SEMESTER_MANAGER,
                Guards::APP_ADMIN,
                SemesterPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::SEMESTER_MANAGER,
                Guards::APP_ADMIN,
                SemesterPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::SEMESTER_MANAGER,
                Guards::APP_ADMIN,
                SemesterPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::SEMESTER_MANAGER,
                Guards::APP_ADMIN,
                SemesterPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::SEMESTER_MANAGER,
                Guards::SCHOOL_ADMIN,
                SemesterPermissions::VIEW,
                "View",
                ""
            )
        ];
    }
}
