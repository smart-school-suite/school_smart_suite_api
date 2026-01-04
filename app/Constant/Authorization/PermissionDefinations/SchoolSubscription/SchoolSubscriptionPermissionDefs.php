<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolSubscription;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolSubscription\SchoolSubscriptionPermissions;

class SchoolSubscriptionPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_SUBSCRIPTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolSubscriptionPermissions::RENEW,
                "Renew",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_SUBSCRIPTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolSubscriptionPermissions::CANCEL,
                "Cancel",
                ""
            )
        ];
    }
}
