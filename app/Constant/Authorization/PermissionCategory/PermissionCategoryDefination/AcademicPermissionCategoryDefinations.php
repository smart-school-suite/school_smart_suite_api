<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AcademicPermissionCategories;

class AcademicPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                AcademicPermissionCategories::COURSE_MANAGER,
                "Course Manager",
                "Allows management of individual course details, syllabus information, and credit assignments."
            ),
            PermissionCategoryBuilder::make(
                AcademicPermissionCategories::DEPARTMENT_MANAGER,
                "Department Manager",
                "Grants administrative control over department-level resources, faculty assignments, and internal policies."
            ),
            PermissionCategoryBuilder::make(
                AcademicPermissionCategories::HALL_MANAGER,
                "Hall Manager",
                "Provides authority to manage physical campus facilities, including lecture halls, exam venues, and room capacities."
            ),
            PermissionCategoryBuilder::make(
                AcademicPermissionCategories::SCHOOL_SEMESTER_MANAGER,
                "Semester Manager",
                "Used to define and manage the academic calendar, including term dates and enrollment periods."
            ),
            PermissionCategoryBuilder::make(
                AcademicPermissionCategories::SEMESTER_TIMETABLE_MANAGER,
                "Semester Time-table Manager",
                "Allows for the creation, editing, and publishing of weekly class schedules and exam timetables."
            ),
            PermissionCategoryBuilder::make(
                AcademicPermissionCategories::SPECIALTY_HALL_MANAGER,
                "Hall Specialty Manager",
                "Enables the mapping of specific academic programs or specialties to their designated physical locations."
            ),
            PermissionCategoryBuilder::make(
                AcademicPermissionCategories::SPECIALTY_MANAGER,
                "Specialty Manager",
                "Provides control over the creation and management of academic majors, tracks, and specialized fields of study."
            ),
        ];
    }
}
