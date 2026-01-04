<?php

namespace App\Constant\System;

class States
{
    public const  INACTIVE = "inactive";
    public const  ACTIVE = "active";
    public const PENDING = "pending";
    public static function all(): array
    {
        return [
            self::INACTIVE,
            self::ACTIVE,
            self::PENDING
        ];
    }
}
