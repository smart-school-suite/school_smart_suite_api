<?php

namespace App\Constant\Authorization\Permissions\ActivationCode;

class ActivationCodePermissions
{
    public const PURCHASE = "activation_code.purchase";
    public const VIEW = "activation_code.view";
    public const ACTIVATE_STUDENT = "activation_code.activate_student";
    public const ACTIVATE_TEACHER = "activation_code.activate_teacher";

    public static function all(): array
    {
        return [
            self::PURCHASE,
            self::VIEW,
            self::ACTIVATE_STUDENT,
            self::ACTIVATE_TEACHER
        ];
    }
}
