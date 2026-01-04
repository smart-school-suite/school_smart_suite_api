<?php

namespace App\Constant\Authorization\PermissionDefinations\TuitionFeePayment;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\TuitionFeePayment\TuitionFeePaymentPermissions;

class TuitionFeePaymentPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeePaymentPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeePaymentPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeePaymentPermissions::PAY,
                "Pay",
                ""
            ),

        ];
    }
}
