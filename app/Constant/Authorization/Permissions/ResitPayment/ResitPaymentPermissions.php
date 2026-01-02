<?php

namespace App\Constant\Authorization\Permissions\ResitPayment;

class ResitPaymentPermissions
{
    public const PAY = "resit_payment.pay";
    public const VIEW = "resit_payment.view";
    public static function all(): array {
         return [
              self::PAY,
              self::VIEW
         ];
    }
}
