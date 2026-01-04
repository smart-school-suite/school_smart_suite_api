<?php

namespace App\Constant\Authorization\PermissionDefinations\TuitionFee;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\TuitionFee\TuitionFeeWaiverPermissions;

class TuitionFeeWaiverPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_WAIVER_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeWaiverPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_WAIVER_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeWaiverPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_WAIVER_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeWaiverPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_WAIVER_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeWaiverPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_WAIVER_MANAGER,
                Guards::STUDENT,
                TuitionFeeWaiverPermissions::VIEW,
                "Student",
                ""
            ),
        ];
    }
}
