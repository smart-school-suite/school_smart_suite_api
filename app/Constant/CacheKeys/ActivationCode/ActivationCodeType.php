<?php

namespace App\Constant\CacheKeys\ActivationCode;

class ActivationCodeType
{
    public const VERSION = 1;
    public const PREFIX = "global:activation_code_type";
    public static function collection(): string
    {
        return self::PREFIX . "collection:v" . self::VERSION;
    }
    public static function byId(string $id)
    {
        return self::PREFIX . "id:{$id}:v" . self::VERSION;
    }
    public static function byCountryId(string $coountryId)
    {
        return self::PREFIX . "country_id:{$coountryId}" . self::VERSION;
    }
}
