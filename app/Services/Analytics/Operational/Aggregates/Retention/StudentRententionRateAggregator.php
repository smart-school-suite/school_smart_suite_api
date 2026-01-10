<?php

namespace App\Services\Analytics\Operational\Aggregates\Retention;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use Illuminate\Support\Collection;

class StudentRententionRateAggregator
{
    public function calculate(Collection $query)
    {
        $enrolledStudents = $query->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)
            ->sum("value");

        $studentDropout = $query->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)
            ->sum("value");

        if ($enrolledStudents <= 0) {
            return 0;
        }

        $retained = $enrolledStudents - $studentDropout;

        return round(($retained / $enrolledStudents) * 100, 2);
    }
}
