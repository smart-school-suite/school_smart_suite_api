<?php

namespace App\Services\Analytics\Operational\Aggregates\Teacher;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use Illuminate\Support\Collection;

class TeacherStudentRatio
{
    public function calculate(Collection $enrolllmentQuery, Collection $operationalQuery)
    {
        $totalTeacher = $operationalQuery->where("kpi", OperationalAnalyticsKpi::TEACHER)->sum("value");
        $totalStudentDropout = $enrolllmentQuery->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)->sum("value");
        $totalStudentEnrolled = $enrolllmentQuery->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)->sum("value");

        $totalStudents = $totalStudentEnrolled - $totalStudentDropout;

        $ratio = ($totalStudents > 0)
            ? ($totalTeacher / $totalStudents)
            : 0;

        return [
            "total_teachers" => $totalTeacher,
            "total_student_dropout" => $totalStudentDropout,
            "total_student_enrolled" => $totalStudentEnrolled,
            "total_students" => $totalStudents,
            "teacher_student_ratio" => round($ratio, 2)
        ];
    }
}
