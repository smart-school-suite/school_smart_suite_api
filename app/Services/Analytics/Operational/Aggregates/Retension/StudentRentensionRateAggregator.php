<?php

namespace App\Services\Analytics\Operational\Aggregates\Retension;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use Illuminate\Support\Collection;

class StudentRentensionRateAggregator
{
    public function calculate(Collection $query){
        $enrolledStudents = $query->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)
            ->sum("value");
        $studentDropout = $query->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)
            ->sum("value");
        $retensionRate = round($enrolledStudents - $studentDropout / $enrolledStudents * 100, 2);
        return $retensionRate;
    }
}
