<?php

namespace App\Services\Analytics\Operational\Widget\StudentRention;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Aggregates\Retension\StudentRentensionRateAggregator;

class StudentRetensionRate
{
    protected StudentRentensionRateAggregator $studentRentensionRateAggregator;
    public function __construct(StudentRentensionRateAggregator $studentRentensionRateAggregator)
    {
        $this->studentRentensionRateAggregator = $studentRentensionRateAggregator;
    }
    public function getStudentRentensionRate($currentSchool)
    {
        $targetKpis = [
            EnrollmentAnalyticsKpi::STUDENT_DROPOUT,
            EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS
        ];

        $query = EnrollmentAnalyticQuery::base($currentSchool->id, $targetKpis);
        return $this->studentRentensionRateAggregator->calculate($query);
    }
}
