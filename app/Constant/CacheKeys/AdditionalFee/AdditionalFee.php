<?php

namespace App\Constant\CacheKeys\AdditionalFee;

class AdditionalFee
{
    public const VERSION = 1;
    public const PREFIX = "additional_fee";
    public static function collection(string $schoolBranchId): string
    {
        return "school_branch:{$schoolBranchId}:{self::PREFIX}:collection:v" . self::PREFIX;
    }
    public static function byStudentId(string $schoolBranchId, string $studentId): string
    {
        return "school_branch:{$schoolBranchId}:{self::PREFIX}:student:{$studentId}:v" . self::PREFIX;
    }
    public static function byId(string $schoolBranchId, string $id): string
    {
        return "school_branch:{$schoolBranchId}:{self::PREFIX}:id:{$id}.v" . self::VERSION;
    }
}
