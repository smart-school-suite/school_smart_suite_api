<?php

namespace App\Constant\Authorization\PermissionDefinations\AdditionalFee;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\AdditionalFee\AdditionalFeePermission;

class AdditionalFeePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeePermission::BILL,
                "Bill",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeePermission::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeePermission::PAY,
                "Pay",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeePermission::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeePermission::VIEW,
                "View",
                ""
            ),
        ];
    }
}
