<?php

namespace App\Services\Analytics\Operational\Widget\StudentDropout;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Aggregates\Dropout\StudentDropoutRateLevelAggregator;

class StudentDropoutRateLevel
{
    protected StudentDropoutRateLevelAggregator $studentDropoutRateLevelAggregator;
    public function __construct(StudentDropoutRateLevelAggregator $studentDropoutRateLevelAggregator)
    {
        $this->studentDropoutRateLevelAggregator = $studentDropoutRateLevelAggregator;
    }
    public function getStudentDropoutRateLevel($currentSchool, $year)
    {
        $targetKpis = [
            EnrollmentAnalyticsKpi::STUDENT_DROPOUT,
            EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS
        ];

        $query = EnrollmentAnalyticQuery::base($currentSchool->id,  $targetKpis);
        $query->where("year", $year);
        return $this->studentDropoutRateLevelAggregator->calculate($query);
    }
}
