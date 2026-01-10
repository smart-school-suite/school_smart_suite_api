<?php

namespace App\Services\Analytics\Operational\Widget\Student;

use App\Services\Analytics\Operational\Aggregates\Student\StudentLevelRegistrationAggregate;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;

class StudentLevelRegistration
{
    protected StudentLevelRegistrationAggregate $studentLevelRegistrationAggregate;
    public function __construct(StudentLevelRegistrationAggregate $studentLevelRegistrationAggregate)
    {
        $this->studentLevelRegistrationAggregate  = $studentLevelRegistrationAggregate;
    }

    public function getStudentLevelRegistration($currentSchool)
    {
        $kpis = [
            EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS
        ];

        $query = EnrollmentAnalyticQuery::base($currentSchool->id, $kpis);
        return $this->studentLevelRegistrationAggregate->calculate($query);
    }
}
