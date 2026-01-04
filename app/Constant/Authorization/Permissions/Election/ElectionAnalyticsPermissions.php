<?php

namespace App\Constant\Authorization\Permissions\Election;

class ElectionAnalyticsPermissions
{
    public const VIEW = "election_analytics.view";
    public static function all(): array
    {
        return [
            self::VIEW,
        ];
    }
}
