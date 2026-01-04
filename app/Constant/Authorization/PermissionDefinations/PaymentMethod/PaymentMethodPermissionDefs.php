<?php

namespace App\Constant\Authorization\PermissionDefinations\PaymentMethod;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\PaymentMethod\PaymentMethodPermissions;

class PaymentMethodPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodPermissions::ACTIVATE,
                "Activate",
                ""
            ),
        ];
    }
}
