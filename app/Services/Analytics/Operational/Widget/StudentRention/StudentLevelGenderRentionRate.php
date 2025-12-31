<?php

namespace App\Services\Analytics\Operational\Widget\StudentRention;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Aggregates\Retension\StudentLevelGenderRentionRate as StudentLevelGenderRentionRateAggregate;

class StudentLevelGenderRentionRate
{
    protected StudentLevelGenderRentionRateAggregate $studentLevelGenderRentionRateAggregate;
    public function __construct(StudentLevelGenderRentionRateAggregate $studentLevelGenderRentionRateAggregate)
    {
        $this->studentLevelGenderRentionRateAggregate = $studentLevelGenderRentionRateAggregate;
    }

    public function getStudentLevelGenderRetensionRate($currentSchool, $filters)
    {
        $targetKpis = [
            EnrollmentAnalyticsKpi::STUDENT_DROPOUT,
            EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS
        ];
        $query  = EnrollmentAnalyticQuery::base($currentSchool->id, $targetKpis);
        $defaultFilters = [
            "gender" => false,
            "level" => true
        ];

        if (empty($filters)) {
            return $this->studentLevelGenderRentionRateAggregate->calculate($query, $defaultFilters);
        }

        return $this->studentLevelGenderRentionRateAggregate->calculate($query, $filters);
    }
}
