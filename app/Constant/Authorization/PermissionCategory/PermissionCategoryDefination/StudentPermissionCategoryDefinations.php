<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\StudentPermissionCategories;

class StudentPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                StudentPermissionCategories::PARENT_MANAGER,
                "Parent Manager",
                "Allows management of guardian profiles, emergency contact details, and the linking of parents to their respective students."
            ),
            PermissionCategoryBuilder::make(
                StudentPermissionCategories::STUDENT_BATCH_MANAGER,
                "Student Batch Manager",
                "Grants authority to manage groups of students by enrollment year or class level, including bulk promotions and graduation processing."
            ),
            PermissionCategoryBuilder::make(
                StudentPermissionCategories::STUDENT_MANAGER,
                "Student Manager",
                "Provides full control over individual student profiles, including admissions, bio-data updates, and status changes (active/withdrawn)."
            ),
            PermissionCategoryBuilder::make(
                StudentPermissionCategories::STUDENT_NOTIFICATION_MANAGER,
                "Student Notification Manager",
                "Allows the creation and delivery of automated alerts, SMS, or email broadcasts specifically targeted at students and their guardians."
            )
        ];
    }
}
