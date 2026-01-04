<?php

namespace App\Constant\Authorization\PermissionDefinations\GradeScale;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\GradeScale\GradeScaleCategoryPermissions;

class GradeScaleCategoryPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::GRADE_SCALE_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                GradeScaleCategoryPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::GRADE_SCALE_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                GradeScaleCategoryPermissions::UPDATE,
                "update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::GRADE_SCALE_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                GradeScaleCategoryPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::GRADE_SCALE_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                GradeScaleCategoryPermissions::DELETE,
                "Delete",
                ""
            ),

        ];
    }
}
