<?php

namespace App\Constant\Authorization\PermissionDefinations\TuitionFee;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\TuitionFee\TuitionFeePermissions;

class TuitionFeePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeePermissions::VIEW_DEBTOR,
                "View Debtor",
                ""
            ),
        ];
    }
}
