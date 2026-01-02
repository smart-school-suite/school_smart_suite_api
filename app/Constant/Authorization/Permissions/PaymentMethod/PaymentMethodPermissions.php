<?php

namespace App\Constant\Authorization\Permissions\PaymentMethod;

class PaymentMethodPermissions
{
    public const CREATE = "payment_method.create";
    public const DELETE = "payment_method.delete";
    public const VIEW = "payment_method.view";
    public const UPDATE = "payment_method.update";
    public const DEACTIVATE = "payment_method.deactivate";
    public const ACTIVATE = "payment_method.activate";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW,
            self::DEACTIVATE,
            self::ACTIVATE
        ];
    }
}
