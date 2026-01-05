<?php

namespace App\Services\Analytics\Operational\Aggregates\Dropout;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use Illuminate\Support\Collection;

class DropoutRateAggregator
{
    public function calculate(Collection $query, $filter)
    {
        $enrolledStudents = $query->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)
            ->sum("value");
        $studentDropout = $query->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)
            ->sum("value");
        return $this->rate($enrolledStudents, $studentDropout);
    }

    protected function rate($enrolledStudents, $studentDropout): float
    {
        if ($enrolledStudents == 0) {
            return 0.0;
        }

        return round(($studentDropout / $enrolledStudents) * 100, 2);
    }
}
