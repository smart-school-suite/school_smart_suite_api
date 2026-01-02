<?php

namespace App\Constant\Authorization\Permissions\Country;

class CountryPermissions
{
    public const CREATE =  "country.create";
    public const UPDATE = "country.update";
    public const DELETE = "country.delete";
    public const VIEW = "country.view";
    public const ACTIVATE = "country.activate";
    public const DEACTIVATE = "country.deactivate";

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
