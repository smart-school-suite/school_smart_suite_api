<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolSettingPermissionCategories;

class SchoolSettingPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_ELECTION_SETTING_MANAGER,
                "School Election Setting Manager",
                "Configures global election protocols, such as voting methods, candidate eligibility requirements, and automated tallying rules."
            ),
            PermissionCategoryBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_EXAM_SETTING_MANAGER,
                "School Exam Setting Manager",
                "Manages institutional exam policies, including default durations, invigilation requirements, and automated seat numbering logic."
            ),
            PermissionCategoryBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_GRADE_SETTING_MANAGER,
                "School Grade Scale Setting Manager",
                "Allows configuration of the master grading system, including GPA calculation methods and rounding rules for the entire school."
            ),
            PermissionCategoryBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_PROMOTION_SETTING_MANAGER,
                "School Promotion Setting Manager",
                "Defines the academic criteria required for students to advance to the next level, such as minimum pass marks and required subjects."
            ),
            PermissionCategoryBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_RESIT_SETTING_MANAGER,
                "School Resit Setting Manager",
                "Sets global policies for supplementary exams, including retake limitations, mark capping, and eligibility criteria."
            ),
            PermissionCategoryBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_SETTING_MANAGER,
                "School Setting Manager",
                "Grants control over core institutional behaviors, including term structures, academic cycles, and primary language/region settings."
            ),
            PermissionCategoryBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_TIMETABLE_SETTING_MANAGER,
                "School Timetable Setting Manager",
                "Configures the structure of the school day, defining standard period durations, break times, and scheduling constraints."
            )
        ];
    }
}
