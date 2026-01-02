<?php

namespace App\Constant\Authorization\Permissions\SchoolExpense;

class SchoolExpensePermissions
{
    public const CREATE = "school_expense.create";
    public const DELETE = "school_expense.delete";
    public const UPDATE = "school_expense.update";
    public const VIEW = "school_expense.view";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW
        ];
    }
}
