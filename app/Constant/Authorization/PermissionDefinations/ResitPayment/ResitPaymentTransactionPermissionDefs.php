<?php

namespace App\Constant\Authorization\PermissionDefinations\ResitPayment;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ResitPayment\ResitPaymentTransactionPermissions;

class ResitPaymentTransactionPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitPaymentTransactionPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_TRANSACTION_MANAGER,
                Guards::STUDENT,
                ResitPaymentTransactionPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitPaymentTransactionPermissions::REVERSE,
                "Reverse",
                ""
            ),
        ];
    }
}
