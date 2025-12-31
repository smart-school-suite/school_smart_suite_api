<?php

namespace App\Services\Analytics\Operational\Widget\StudentDropout;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Aggregates\Dropout\StudentDropoutLevelGenderAggregator;

class StudentDropoutLevelGender
{
    protected StudentDropoutLevelGenderAggregator $studentDropoutLevelGenderAggregator;
    public function __construct(StudentDropoutLevelGenderAggregator $studentDropoutLevelGenderAggregator)
    {
        $this->studentDropoutLevelGenderAggregator = $studentDropoutLevelGenderAggregator;
    }
    public function getStudentDropoutLevelGender($currentSchool, $year, $filters)
    {
        $targetKpis = [
            EnrollmentAnalyticsKpi::STUDENT_DROPOUT,
            EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS
        ];

        $defaultFilters = [
            "gender" => false,
            "level" => true
        ];

        $query = EnrollmentAnalyticQuery::base($currentSchool->id,  $targetKpis);
        $query->where("year", $year);
        if (empty($filters)) {
            return $this->studentDropoutLevelGenderAggregator->calculate($query, $defaultFilters);
        }

        if (!empty($filters)) {
            return $this->studentDropoutLevelGenderAggregator->calculate($query, $filters);
        }
    }
}
