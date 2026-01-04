<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolSubscription;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolSubscription\SchoolSubscriptionTransactionPermissions;

class SchoolSubscriptionTranasactionPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_SUBSCRIPTION_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolSubscriptionTransactionPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_SUBSCRIPTION_TRANSACTION_MANAGER,
                Guards::APP_ADMIN,
                SchoolSubscriptionTransactionPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_SUBSCRIPTION_TRANSACTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolSubscriptionTransactionPermissions::DELETE,
                "Delete",
                ""
            ),
        ];
    }
}
