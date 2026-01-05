<?php

namespace App\Constant\CacheKeys\SchoolExpense;

class SchoolExpenseCategory
{
    public const VERSION = 1;
    public const PREFIX = "school_expense_category";
    public static function collectionActive(string $schoolBranchId): string
    {
        return "school_branch:{$schoolBranchId}:{self::PREFIX}:collection:active:v" . self::VERSION;
    }
    public static function collection(string $schoolBranchId): string
    {
        return "school_branch:{$schoolBranchId}:{self::PREFIX}:collection:v" . self::VERSION;
    }
    public static function byId(string $schoolBranchId, string $id): string
    {
        return "school_branch:{$schoolBranchId}:{self::PREFIX}:id:{$id}:v" . self::VERSION;
    }
}
