<?php

namespace App\Constant\Authorization\PermissionDefinations\TuitionFeePayment;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\TuitionFeePayment\TuitionFeePaymentTransactionPermissions;

class TuitionFeePaymentTransactionPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeePaymentTransactionPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeePaymentTransactionPermissions::REVERSE,
                "Reverse",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeePaymentTransactionPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::TUITION_FEE_TRANSACTION_MANAGER,
                Guards::STUDENT,
                TuitionFeePaymentTransactionPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
