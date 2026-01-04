<?php

namespace App\Constant\Authorization\PermissionDefinations\ExamResult;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ExamResult\ExamResultPermissions;

class ExamResultPermissionsDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_RESULT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamResultPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_RESULT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamResultPermissions::DELETE,
                "Delete",
                ""
            ),
        ];
    }
}
