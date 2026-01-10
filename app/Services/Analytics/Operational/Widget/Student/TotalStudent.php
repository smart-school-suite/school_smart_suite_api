<?php

namespace App\Services\Analytics\Operational\Widget\Student;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Aggregates\Student\TotalStudent as TotalStudentAggregate;
class TotalStudent
{
    protected TotalStudentAggregate $totalStudentAggregate;
    public function __construct(TotalStudentAggregate $totalStudentAggregate)
    {
        $this->totalStudentAggregate = $totalStudentAggregate;
    }
    public function getTotalStudents($currentSchool){
         $kpis = [
             EnrollmentAnalyticsKpi::STUDENT_DROPOUT,
             EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS
         ];

         $query = EnrollmentAnalyticQuery::base($currentSchool->id, $kpis);
         return $this->totalStudentAggregate->calculate($query);
    }
}
