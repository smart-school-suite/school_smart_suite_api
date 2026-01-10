<?php

namespace App\Services\Analytics\Operational\Widget\StudentRetention;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Aggregates\Retention\StudentLevelRetentionRateAggregate;

class StudentLevelRetentionRate
{
    protected StudentLevelRetentionRateAggregate $studentLevelRetentionRateAggregate;
    public function __construct(StudentLevelRetentionRateAggregate $studentLevelRetentionRateAggregate)
    {
        $this->studentLevelRetentionRateAggregate = $studentLevelRetentionRateAggregate;
    }

    public function getStudentLevelRetentionRate($currentSchool)
    {
        $targetKpis = [
            EnrollmentAnalyticsKpi::STUDENT_DROPOUT,
            EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS
        ];
        $query = EnrollmentAnalyticQuery::base($currentSchool->id, $targetKpis);

        return $this->studentLevelRetentionRateAggregate->calculate($query);
    }
}
