<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;

class SystemPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::ACTIVATION_CODE_TYPE_MANAGER,
                "Activation Code Type Manager",
                "Defines the various categories of system keys, such as 'One-time', 'Subscription-based', or 'Trial' codes."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::COUNTRY_MANAGER,
                "Country Manager",
                "Manages the global list of countries, dialing codes, and regional formats used for addresses and phone numbers."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::EXAM_TYPE_MANAGER,
                "Exam Type Manager",
                "Allows the definition of standard assessment formats, such as 'Mid-term', 'Final', 'Quiz', or 'Practical'."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::FEATURE_MANAGER,
                "Feature Manager",
                "Used to define and control individual system capabilities that can be enabled or disabled for the institution."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::GENDER_MANAGER,
                "Gender Manager",
                "Manages the standard gender identity options available across student, staff, and parent profiles."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::GRADE_SCALE_CATEGORY_MANAGER,
                "Grade Scale Category Manager",
                "Enables the categorization of grading systems, such as 'Standard Letter', 'Percentage', or 'Pass/Fail'."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::LETTER_GRADE_MANAGER,
                "Letter Grade Manager",
                "Allows management of the specific characters used in grading (e.g., A+, B, C) and their corresponding descriptions."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_CATEGORY_MANAGER,
                "Payment Method Category Manager",
                "Organizes payment options into groups such as 'Digital/Online', 'Bank Transfer', or 'Physical Cash'."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::PAYMENT_METHOD_MANAGER,
                "Payment Method Manager",
                "Used to configure specific payment gateways and options available for tuition and fee collections."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                "Permission Category Manager",
                "Provides high-level control over how various system permissions are grouped and displayed to administrators."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::PERMISSION_MANAGER,
                "Permission Manager",
                "Grants authority to define specific granular actions (Create, Read, Update, Delete) within the system's security logic."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::PLAN_FEATURE_MANAGER,
                "Plan Feature Manager",
                "Maps specific system features to subscription plans, defining what is available in Basic vs. Premium tiers."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::PLAN_MANAGER,
                "Plan Manager",
                "Allows for the creation and management of institutional subscription packages and service levels."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::ROLE_MANAGER,
                "Role Manager",
                "Enables the creation of system roles (e.g., 'Lecturer', 'Bursar') that serve as templates for user permissions."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::SEMESTER_MANAGER,
                "Semester Manager",
                "Defines the naming and sequence of academic terms (e.g., Fall, Spring, Summer) used across the platform."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::STUDENT_PARENT_RELATIONSHIP_MANAGER,
                "Student Parent Relationship Manager",
                "Manages the standard types of family connections, such as 'Father', 'Mother', 'Legal Guardian', or 'Sponsor'."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::STUDENT_SOURCE_MANAGER,
                "Student Source Manager",
                "Used to track and manage student lead sources, such as 'Referral', 'Website', 'Agent', or 'Direct Application'."
            ),
            PermissionCategoryBuilder::make(
                SystemPermissionCategories::TUITION_FEE_INSTALLMENT_MANAGER,
                "Tuition Fee Installment Manager",
                "Allows the creation and management of payment plans, enabling tuition to be paid in multiple parts."
            ),
        ];
    }
}
