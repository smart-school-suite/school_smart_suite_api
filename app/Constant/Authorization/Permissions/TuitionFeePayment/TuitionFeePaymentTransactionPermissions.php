<?php

namespace App\Constant\Authorization\Permissions\TuitionFeePayment;

class TuitionFeePaymentTransactionPermissions
{
    public const REVERSE = "tuition_fee_transaction.reverse";
    public const DELETE = "tuition_fee_transaction.delete";
    public const VIEW = "tuition_fee_transaction.view";

    public static function all(): array
    {
        return [
            self::REVERSE,
            self::DELETE,
            self::VIEW
        ];
    }
}
