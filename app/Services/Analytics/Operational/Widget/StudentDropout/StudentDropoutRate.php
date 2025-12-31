<?php

namespace App\Services\Analytics\Operational\Widget\StudentDropout;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Filter\StudentDropoutRateFilter;
use App\Services\Analytics\Operational\Aggregates\Dropout\DropoutRateAggregator;
class StudentDropoutRate
{
   protected StudentDropoutRateFilter $studentDropoutRateFilter;
   protected DropoutRateAggregator $dropoutRateAggregator;
   public function __construct(StudentDropoutRateFilter $studentDropoutRateFilter, DropoutRateAggregator $dropoutRateAggregator)
   {
      $this->studentDropoutRateFilter = $studentDropoutRateFilter;
      $this->dropoutRateAggregator = $dropoutRateAggregator;
   }
   public function getStudentDropoutRate($currentSchool, $year, $filters){
       $enrollmentKpis = [
          EnrollmentAnalyticsKpi::STUDENT_DROPOUT,
          EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS
       ];
       $enrollmentQuery = EnrollmentAnalyticQuery::base($currentSchool->id,  $enrollmentKpis);
       $enrollmentQuery->where("year", $year);
       $enrollmentQuery = $this->studentDropoutRateFilter->apply($enrollmentQuery, $filters);
       //call aggregator and return value
       return $this->dropoutRateAggregator->calculate($enrollmentQuery, $filters);
   }
}
