<?php

namespace App\Constant\Authorization\PermissionDefinations\AdditionalFee;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\AdditionalFee\AdditionalFeeCategoryPermissions;

class AdditionalFeeCategoryPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeeCategoryPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeeCategoryPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeeCategoryPermissions::ACTIVATE,
                "Activate",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeeCategoryPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeeCategoryPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::ADDITIONAL_FEE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AdditionalFeeCategoryPermissions::UPDATE,
                "Update",
                ""
            )

        ];
    }
}
