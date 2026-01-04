<?php

namespace App\Constant\Authorization\PermissionDefinations\ResitPayment;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ResitPayment\ResitPaymentPermissions;

class ResitPaymentPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::RESIT_PAYMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitPaymentPermissions::PAY,
                "Pay",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::RESIT_PAYMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitPaymentPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
