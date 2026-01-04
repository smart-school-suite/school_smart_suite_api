<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolExpense;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolExpense\SchoolExpensePermissions;

class SchoolExpensePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_EXPENSE_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolExpensePermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_EXPENSE_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolExpensePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_EXPENSE_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolExpensePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                FinancePermissionCategories::SCHOOL_EXPENSE_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolExpensePermissions::UPDATE,
                "Update",
                ""
            ),
        ];
    }
}
