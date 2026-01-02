<?php

namespace App\Constant\Authorization\Permissions\RegistrationFee;

class RegistrationFeeTransactionPermissions
{
    public const REVERSE = "registration_fee.reverse";
    public const DELETE = "registration_fee.delete";
    public const VIEW = "registration_fee.view";

    public static function all(): array
    {
        return [
            self::REVERSE,
            self::DELETE,
            self::VIEW
        ];
    }
}
