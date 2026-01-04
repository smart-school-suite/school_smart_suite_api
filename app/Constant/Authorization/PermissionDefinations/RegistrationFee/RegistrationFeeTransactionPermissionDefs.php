<?php

namespace App\Constant\Authorization\PermissionDefinations\RegistrationFee;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\RegistrationFee\RegistrationFeeTransactionPermissions;

class RegistrationFeeTransactionPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                RegistrationFeeTransactionPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                RegistrationFeeTransactionPermissions::REVERSE,
                "Reverse",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                RegistrationFeeTransactionPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_TRANSACTION_MANAGER,
                Guards::STUDENT,
                RegistrationFeeTransactionPermissions::VIEW,
                "View",
                ""
            )
        ];
    }
}
