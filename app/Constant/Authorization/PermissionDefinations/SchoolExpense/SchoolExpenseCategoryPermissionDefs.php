<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolExpense;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolExpense\SchoolExpenseCategoryPermissions;

class SchoolExpenseCategoryPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_EXPENSE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolExpenseCategoryPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_EXPENSE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolExpenseCategoryPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_EXPENSE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolExpenseCategoryPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_EXPENSE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolExpenseCategoryPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_EXPENSE_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolExpenseCategoryPermissions::ACTIVATE,
                "Activate",
                ""
            ),
        ];
    }
}
