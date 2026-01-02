<?php

namespace App\Constant\Authorization\Permissions\ActivationCode;

class ActivationCodeTypePermissions
{
    public const CREATE = "activation_code_type.create";
    public const DELETE = "activation_code_type.delete";
    public const DEACTIVATE = "activation_code_type.deactivate";
    public const ACTIVATE = "activation_code_type.activate";
    public const UPDATE = "activation_code_type.update";
    public const VIEW = "activation_code_type.view";
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
