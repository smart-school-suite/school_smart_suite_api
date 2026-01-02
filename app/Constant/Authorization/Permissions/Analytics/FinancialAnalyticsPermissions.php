<?php

namespace App\Constant\Authorization\Permissions\Analytics;

class FinancialAnalyticsPermissions
{
   public const VIEW = "financial_analytics.view";
   public static function all(): array {
       return [
           self::VIEW
       ];
   }
}
