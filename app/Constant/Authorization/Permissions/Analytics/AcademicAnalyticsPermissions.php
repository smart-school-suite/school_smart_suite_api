<?php

namespace App\Constant\Authorization\Permissions\Analytics;

class AcademicAnalyticsPermissions
{
    public const VIEW_SCHOOL =  "school_academic_analytics.view";
    public const VIEW_STUDENT = "student_academic_analaytics.view";

    public static function all(): array {
         return [
             self::VIEW_SCHOOL,
             self::VIEW_STUDENT
         ];
    }
}
