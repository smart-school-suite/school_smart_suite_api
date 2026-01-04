<?php

namespace App\Constant\Authorization\PermissionDefinations\AdditionalFee;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\AdditionalFee\AdditionalFeeTransactionPermission;

class AdditionalFeeTransactionPermissionsDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeeTransactionPermission::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeeTransactionPermission::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeeTransactionPermission::REVERSE,
                "Reverse",
                ""
            ),
        ];
    }
}
