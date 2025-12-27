<?php

namespace App\Constant\Analytics\Operational;

class OperationalAnalyticsEvent
{
    public const STUDENT_DROPOUT = "operational.student_dropout";
    public const TEACHER_DROPOUT = "operational.teacher_dropout";
    public const STAFF_DROPOUT = "operational.staff_dropout";
    public const COURSE_CREATED = "operational.course.created";
    public const DEPARTMENT_CREATED = "operational.department.created";
    public const TEACHER_CREATED = "operational.teacher.created";
    public const HALL_CREATED = "operational.hall.created";
    public const HALL_ACTIVATED = "operational.hall.activated";
    public const HALL_DEACTIVATED = "operational.hall.deactivated";
    public const TEACHER_COURSE_ASSIGNED = "operational.teacher.course.assigned";
    public const TEACHER_SPECIALTY_ASSIGNED = "operational.teacher.specialty.assigned";
    public const SPECIALTY_CREATED = "operational.specialty.created";
    public const SPECIALTY_DEACTIVATED = "operational.specialty.deactivated";
    public const SPECIALTY_ACTIVATED = "operational.specialty.activated";
    public const DEPARTMENT_DEACTIVATED = "operational.department.deactivated";
    public const DEPARTMENT_ACTIVATED = "operational.department.activated";
}
