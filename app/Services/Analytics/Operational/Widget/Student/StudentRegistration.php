<?php

namespace App\Services\Analytics\Operational\Widget\Student;

use App\Services\Analytics\Operational\Aggregates\Student\StudentRegistrationAggregate;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;

class StudentRegistration
{
    // Implement your logic here
    protected StudentRegistrationAggregate $studentRegistrationAggregate;
    public function __construct(StudentRegistrationAggregate $studentRegistrationAggregate)
    {
        $this->studentRegistrationAggregate = $studentRegistrationAggregate;
    }

    public function getStudentRegistration($currentSchool, $year)
    {
        $kpis = [
            EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS
        ];
        $query = EnrollmentAnalyticQuery::base($currentSchool->id, $kpis);
        return $this->studentRegistrationAggregate->calculate($query);
    }
}
