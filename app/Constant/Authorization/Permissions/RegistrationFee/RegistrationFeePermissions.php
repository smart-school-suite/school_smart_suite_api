<?php

namespace App\Constant\Authorization\Permissions\RegistrationFee;

class RegistrationFeePermissions
{
    public const PAY = "registration_fee.pay";
    public const DELETE = "registration_fee.delete";
    public const VIEW = "registration_fee.pay";
    public static function all(): array
    {
        return [
            self::PAY,
            self::DELETE,
            self::VIEW
        ];
    }
}
