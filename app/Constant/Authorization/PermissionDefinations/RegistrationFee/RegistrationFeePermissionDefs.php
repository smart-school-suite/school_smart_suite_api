<?php

namespace App\Constant\Authorization\PermissionDefinations\RegistrationFee;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\RegistrationFee\RegistrationFeePermissions;

class RegistrationFeePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                RegistrationFeePermissions::PAY,
                "Pay",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                RegistrationFeePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_MANAGER,
                Guards::STUDENT,
                RegistrationFeePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::REGISTRATION_FEE_MANAGER,
                Guards::SCHOOL_ADMIN,
                RegistrationFeePermissions::DELETE,
                "Delete",
                ""
            ),
        ];
    }
}
