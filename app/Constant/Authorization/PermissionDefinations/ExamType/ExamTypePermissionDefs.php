<?php

namespace App\Constant\Authorization\PermissionDefinations\ExamType;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ExamType\ExamTypePermissions;

class ExamTypePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::EXAM_TYPE_MANAGER,
                Guards::APP_ADMIN,
                ExamTypePermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::EXAM_TYPE_MANAGER,
                Guards::APP_ADMIN,
                ExamTypePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::EXAM_TYPE_MANAGER,
                Guards::APP_ADMIN,
                ExamTypePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::EXAM_TYPE_MANAGER,
                Guards::APP_ADMIN,
                ExamTypePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::EXAM_TYPE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamTypePermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
