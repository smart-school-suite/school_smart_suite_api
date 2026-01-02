<?php

namespace App\Constant\Authorization\Permissions\Analytics;

class OperationalAnalyticsPermissions
{
    public const VIEW = "operational_analytics.view";
    public static function all(): array
    {
        return [
            self::VIEW
        ];
    }
}
