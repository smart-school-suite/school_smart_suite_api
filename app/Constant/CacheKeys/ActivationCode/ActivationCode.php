<?php

namespace App\Constant\CacheKeys\ActivationCode;

class ActivationCode
{
    public const VERSION = 1;

    public static function collection(string $schoolBranchId): string
    {
        return "school_branch:{$schoolBranchId}:collection:v" . self::VERSION;
    }
    public static function byId(string $id, $schoolBranchId)
    {
        return "school_branch:{$schoolBranchId}:id:{$id}:v" . self::VERSION;
    }
}
