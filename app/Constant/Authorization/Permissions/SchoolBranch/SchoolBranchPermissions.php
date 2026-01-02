<?php

namespace App\Constant\Authorization\Permissions\SchoolBranch;

class SchoolBranchPermissions
{
    public const VIEW = "school_branch.view";
    public const UPDATE = "school_branch.update";
    public const DELETE = "school_branch.delete";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::UPDATE,
            self::DELETE
        ];
    }
}
