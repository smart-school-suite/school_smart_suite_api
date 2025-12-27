<?php

namespace App\Services\Analytics;

use App\Models\Analytics\Operational\OperationalAnalyticSnapshot;
use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use App\Models\Department;
use App\Models\Gender;
use App\Models\Specialty;

class OperationalAnalyticsService
{
    public function getOperationalAnalytics($currentSchool, $year)
    {
        return [
            "total_specialties" => self::getTotalSpecialties($currentSchool),
            "total_courses" => self::getTotalCourses($currentSchool),
            "active_specialties" => self::getActiveSpecialties($currentSchool),
            "deactivated_specialties" => self::getDeactivatedSpecialties($currentSchool),
            "total_department" => self::getTotalDepartment($currentSchool),
            "total_inactive_department" => self::getInactiveDepartment($currentSchool),
            "total_active_department" => self::getInactiveDepartment($currentSchool),
            "total_student_dropout" => self::getStudentDropout($currentSchool, $year),
            "total_student_debt_dropout" => self::getStudentDropoutDept($currentSchool, $year),
            "total_student_gender_dropout" => self::studentGenderDropout($currentSchool, $year),
            "teacher_specialty_count" => self::getSpecialtyTeacherCount($currentSchool),
            "total_hall" => self::getTotalHalls($currentSchool),
            "total_active_hall" => self::getTotalActiveHall($currentSchool),
            "totla_inactive" => self::getTotalInactiveHall($currentSchool)
        ];
    }

    protected static function getTotalSpecialties($currentSchool)
    {
        $totalSpecialties =   OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::SPECIALTY)
            ->first();
        return $totalSpecialties->value ?? 0;
    }
    protected static function getTotalCourses($currentSchool)
    {
        $totalCourses = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::COURSE)
            ->first();
        return $totalCourses->value ?? 0;
    }
    protected static function getActiveSpecialties($currentSchool)
    {
        $activeSpecialty = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::ACTIVE_SPECIALTY)
            ->first();
        return $activeSpecialty->value ?? 0;
    }
    protected static function getDeactivatedSpecialties($currentSchool)
    {
        $deactivatedSpecialties = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::INACTIVE_SPECIALTY)
            ->first();
        return $deactivatedSpecialties ?? 0;
    }
    protected static function getTotalDepartment($currentSchool)
    {
        $totalDepartment = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::DEPARTMENT)
            ->first();
        return $totalDepartment->value ?? 0;
    }
    protected static function getInactiveDepartment($currentSchool)
    {
        $totalInactiveDepartment = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::INACTIVE_DEPARTMENT)
            ->first();
        return $totalInactiveDepartment->value ?? 0;
    }
    protected static function getActiveDepartment($currentSchool)
    {
        $inactiveDepartment = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::ACTIVE_DEPARTMENT)
            ->first();
        return $inactiveDepartment->value ?? 0;
    }
    protected static function getStudentDropout($currentSchool, $year)
    {
        $studentDropout = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::STUDENT_DROPOUT)
            ->where("year", $year)
            ->first();
        return $studentDropout->value ?? 0;
    }
    protected static function getStudentDropoutDept($currentSchool, $year)
    {
        $studentDeptDropout = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::STUDENT_DEPARTMENT_DROPOUT)
            ->where("year", $year)
            ->orderByDesc('value')
            ->take(5)
            ->get();
        if ($studentDeptDropout->isEmpty()) {
            return [];
        }
        return $studentDeptDropout->map(fn($dropout) => [
            "department_id" => $dropout->id,
            "department" => Department::find($dropout->department_id)->department_name ?? "unknown"
        ]);
    }
    protected static function studentGenderDropout($currentSchool, $year)
    {
        $studentGenDropout = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::STUDENT_GENDER_DROPOUT)
            ->where("year", $year)
            ->get();
        if ($studentGenDropout->isEmpty()) {
            return [];
        }
        return $studentGenDropout->map(fn($dropout) => [
            "gender_id" => $dropout->gender_id,
            "gender" => Gender::find($dropout->gender_id)->name ?? "unknown",
            "value" => $dropout->value
        ]);
    }
    protected static function getSpecialtyTeacherCount($currentSchool)
    {
        $teacherSpecCount = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::TEACHER_SPECIALTY_COUNT)
            ->get();
        if ($teacherSpecCount->isEmpty()) {
            return [];
        }

        return $teacherSpecCount->map(fn($spec) => [
            "specialty_id" => $spec->specialy_id,
            "specialty" => Specialty::find($spec->specialty_id)->specialty_name ?? "unknown",
            "value" => $spec->value
        ]);
    }
    protected static function getTeacherGenderCount($currentSchool)
    {
        $teacherGenders = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::TEACHER_GENDER)
            ->get();

        if ($teacherGenders->isEmpty()) {
            return [];
        }

        return $teacherGenders->map(fn($teacherGender) => [
            "gender_id" => $teacherGender->gender_id,
            "gender" => Gender::find($teacherGender->gender_id)->name ?? "unknown",
            "value" => $teacherGender->value ?? 0
        ]);
    }
    protected static function getTotalHalls($currentSchool)
    {
        $totalHalls = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::HALL)
            ->first();
        return $totalHalls->value ?? 0;
    }
    protected static function getTotalActiveHall($currentSchool)
    {
        $totalHalls = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::ACTIVE_HALL)
            ->first();
        return $totalHalls->value ?? 0;
    }
    protected static function getTotalInactiveHall($currentSchool)
    {
        $totalHall = OperationalAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", OperationalAnalyticsKpi::INACTIVE_HALL)
            ->first();
        return $totalHall->value ?? 0;
    }
}
