<?php

namespace App\Constant\Authorization\Permissions\PaymentMethod;

class PaymentMethodCategoryPermissions
{
    public const CREATE = "payment_method_category.create";
    public const DELETE = "payment_method_category.delete";
    public const VIEW = "payment_method_category.view";
    public const UPDATE = "payment_method_category.update";
    public const DEACTIVATE = "payment_method_category.deactivate";
    public const ACTIVATE = "payment_method_category.activate";

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
