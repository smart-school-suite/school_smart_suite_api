<?php

namespace App\Services\Analytics\Operational\Widget\StudentRetention;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Aggregates\Retention\StudentRententionRateAggregator;

class StudentRetentionRate
{
    protected StudentRententionRateAggregator $studentRententionRateAggregator;
    public function __construct(StudentRententionRateAggregator $studentRententionRateAggregator)
    {
        $this->studentRententionRateAggregator = $studentRententionRateAggregator;
    }
    public function getStudentRententionRate($currentSchool)
    {
        $targetKpis = [
            EnrollmentAnalyticsKpi::STUDENT_DROPOUT,
            EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS
        ];

        $query = EnrollmentAnalyticQuery::base($currentSchool->id, $targetKpis);
        return $this->studentRententionRateAggregator->calculate($query);
    }
}
