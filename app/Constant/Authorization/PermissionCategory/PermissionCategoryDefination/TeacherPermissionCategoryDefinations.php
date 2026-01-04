<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\TeacherPermissionCategories;

class TeacherPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                TeacherPermissionCategories::TEACHER_COURSE_MANAGER,
                "Teacher Course Manager",
                "Allows the assignment of teachers to specific courses and the management of their teaching workloads for each term."
            ),
            PermissionCategoryBuilder::make(
                TeacherPermissionCategories::TEACHER_MANAGER,
                "Teacher Manager",
                "Grants authority to manage faculty profiles, including personal records, employment status, and professional credentials."
            ),
            PermissionCategoryBuilder::make(
                TeacherPermissionCategories::TEACHER_NOTIFICATION_MANAGER,
                "Teacher Notification Manager",
                "Enables the sending of targeted internal communications, staff bulletins, and automated policy alerts to the teaching staff."
            ),
            PermissionCategoryBuilder::make(
                TeacherPermissionCategories::TEACHER_SPECIALTY_MANAGER,
                "Teacher Specialty Manager",
                "Used to define a teacher's areas of expertise, ensuring they are only assigned to courses within their qualified academic fields."
            ),
            PermissionCategoryBuilder::make(
                TeacherPermissionCategories::TEACHER_TIME_PREFERENCE_MANAGER,
                "Teacher Time Preference Manager",
                "Allows management of teacher availability and scheduling constraints, used to inform the automated timetable generator."
            )
        ];
    }
}
