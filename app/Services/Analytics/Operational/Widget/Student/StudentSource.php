<?php

namespace App\Services\Analytics\Operational\Widget\Student;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Aggregates\Student\StudentSource as StudentSourceAggregate;

class StudentSource
{
    protected StudentSourceAggregate $studentSourceAggregate;
    public function __construct(StudentSourceAggregate $studentSourceAggregate)
    {
        $this->studentSourceAggregate = $studentSourceAggregate;
    }

    public function getStudentSource($currentSchool, $year)
    {
        $kpis = [
            EnrollmentAnalyticsKpi::STUDENT_ENROLLEMENT_SOURCE,
        ];

        $query = EnrollmentAnalyticQuery::base($currentSchool->id, $kpis);
        return $this->studentSourceAggregate->calculate($query);
    }
}
