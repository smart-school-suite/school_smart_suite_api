<?php

namespace App\Constant\Authorization\Permissions\SchoolExpense;

class SchoolExpenseCategoryPermissions
{
    public const CREATE = "school_expense_category.create";
    public const VIEW  = "school_expense_category.view";
    public const UPDATE = "school_expense_category.update";
    public const DELETE = "school_expense_category.delete";
    public const ACTIVATE = "school_expense_category.activate";
    public const DEACTIVATE = "school_expense_category.deactivate";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW,
            self::DEACTIVATE,
            self::ACTIVATE
        ];
    }
}
