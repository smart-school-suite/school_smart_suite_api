<?php

namespace App\Constant\Authorization\Permissions\TuitionFeePayment;

class TuitionFeePaymentPermissions
{
    public const PAY = "tuition_fee.pay";
    public const VIEW = "tuition_fee.view";
    public const DELETE = "tuition_fee.delete";
    public static function all(): array
    {
        return [
            self::PAY,
            self::VIEW,
            self::DELETE
        ];
    }
}
