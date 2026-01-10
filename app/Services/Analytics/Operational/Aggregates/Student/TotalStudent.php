<?php

namespace App\Services\Analytics\Operational\Aggregates\Student;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use Illuminate\Support\Collection;

class TotalStudent
{
    public static function calculate(Collection $query)
    {
        $enrolledStudents = $query->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)->sum("value");
        $dropoutStudents = $query->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)->sum("value");
        return [
            "enrolled_students" => $enrolledStudents,
            "dropout_students" => $dropoutStudents,
            "current_students" => $enrolledStudents - $dropoutStudents,
        ];
    }
}
