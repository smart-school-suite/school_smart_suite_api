<?php

namespace App\Constant\Authorization\PermissionDefinations\ResitEvaluation;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ResitEvaluation\ResitResultPermissions;

class ResitResultPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_RESULT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitResultPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_RESULT_MANAGER,
                Guards::STUDENT,
                ResitResultPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_RESULT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitResultPermissions::DELETE,
                "Delete",
                ""
            )
        ];
    }
}
