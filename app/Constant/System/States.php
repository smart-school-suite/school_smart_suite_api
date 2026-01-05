<?php

namespace App\Constant\System;

class States
{
    public const  INACTIVE = "inactive";
    public const  ACTIVE = "active";
    public const PENDING = "pending";
    public const ONGOING  = "ongoing";
    public const ENDED = "ended";
    public const UPCOMING = "upcoming";
    public const FINISHED  = "finished";
    public static function all(): array
    {
        return [
            self::INACTIVE,
            self::ACTIVE,
            self::PENDING
        ];
    }
}
