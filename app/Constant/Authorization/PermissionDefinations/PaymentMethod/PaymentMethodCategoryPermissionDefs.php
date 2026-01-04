<?php

namespace App\Constant\Authorization\PermissionDefinations\PaymentMethod;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\PaymentMethod\PaymentMethodCategoryPermissions;

class PaymentMethodCategoryPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodCategoryPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodCategoryPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodCategoryPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodCategoryPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodCategoryPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                PaymentMethodCategoryPermissions::ACTIVATE,
                "Activate",
                ""
            ),
        ];
    }
}
