<?php

namespace App\Constant\Authorization\Permissions\AdditionalFee;

class AdditionalFeeTransactionPermission
{
    public const VIEW = "additional_fee_transaction.view";
    public const DELETE = "additional_fee_transaction.delete";
    public const REVERSE = "additional_fee_transaction.reverse";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::DELETE,
            self::REVERSE
        ];
    }
}
