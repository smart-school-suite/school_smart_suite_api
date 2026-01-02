<?php

namespace App\Constant\Authorization\Permissions\ResitPayment;

class ResitPaymentTransactionPermissions
{
    public const DELETE = "resit_payment_transaction.delete";
    public const REVERSE = "resit_payment_transaction.reverse";
    public const VIEW = "resit_payment_transaction.view";

    public static function all(): array
    {
        return [
            self::DELETE,
            self::REVERSE,
            self::VIEW
        ];
    }
}
