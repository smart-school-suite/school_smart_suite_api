<?php

namespace App\Services\Analytics\Operational\Aggregates\Teacher;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use Laravel\Scout\Builder;

class TeacherStudentRatio
{
    public function calculate(Builder $enrolllmentQuery, Builder $operationalQuery){
          $totalTeacher = $operationalQuery->where("kpi", OperationalAnalyticsKpi::TEACHER)->sum("value");
          $totalStudentDropout = $enrolllmentQuery->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)->sum("value");
          $totalStudentEnrolled = $enrolllmentQuery->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)->sum("value");
          return [
                "total_teachers" => $totalTeacher,
                "total_student_dropout" => $totalStudentDropout,
                "total_student_enrolled" => $totalStudentEnrolled,
                "total_students" => $totalStudentEnrolled - $totalStudentDropout,
                "teacher_student_ratio" => round($totalTeacher/ $totalStudentEnrolled - $totalStudentDropout, 2)
          ];
    }
}
