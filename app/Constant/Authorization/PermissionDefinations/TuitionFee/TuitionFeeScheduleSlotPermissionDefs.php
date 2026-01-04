<?php

namespace App\Constant\Authorization\PermissionDefinations\TuitionFee;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\TuitionFee\TuitionFeeScheduleSlotPermissions;

class TuitionFeeScheduleSlotPermissionDefs
{
    public static function all(): array
    {
        return  [
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_SCHEDULE_SLOT_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeScheduleSlotPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_SCHEDULE_SLOT_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeScheduleSlotPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_SCHEDULE_SLOT_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeScheduleSlotPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_SCHEDULE_SLOT_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeScheduleSlotPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_SCHEDULE_SLOT_MANAGER,
                Guards::STUDENT,
                TuitionFeeScheduleSlotPermissions::VIEW,
                "View",
                ""
            ),

        ];
    }
}
