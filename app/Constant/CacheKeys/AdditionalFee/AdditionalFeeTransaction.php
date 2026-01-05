<?php

namespace App\Constant\CacheKeys\AdditionalFee;

class AdditionalFeeTransaction
{
    public const VERSION = 1;
    public const PREFIX = "additional_fee_transaction";
    public static function collection(string $schoolBranchId): string
    {
        return "school_branch:{$schoolBranchId}:{self::PREFIX}:collection:v" . self::VERSION;
    }
    public static function byId(string $schoolBranchId, string $id): string
    {
        return "school_branch:{$schoolBranchId}:{self::PREFIX}:id:{$id}:v" . self::VERSION;
    }
}
