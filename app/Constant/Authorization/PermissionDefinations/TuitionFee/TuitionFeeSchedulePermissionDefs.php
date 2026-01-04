<?php

namespace App\Constant\Authorization\PermissionDefinations\TuitionFee;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\TuitionFee\TuitionFeeSchedulePermissions;

class TuitionFeeSchedulePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_SCHEDULE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeSchedulePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_SCHEDULE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeSchedulePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_SCHEDULE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeSchedulePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_SCHEDULE_MANAGER,
                Guards::STUDENT,
                TuitionFeeSchedulePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_SCHEDULE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeSchedulePermissions::CREATE,
                "Create",
                ""
            ),

        ];
    }
}
