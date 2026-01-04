<?php

namespace App\Constant\Authorization\PermissionDefinations\Resit;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Resit\ResitPermissions;

class ResitPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_MANAGER,
                Guards::STUDENT,
                ResitPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitPermissions::DELETE,
                "Delete",
                ""
            ),
        ];
    }
}
