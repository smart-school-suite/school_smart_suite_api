<?php

namespace App\Services\Analytics\Academic\Widget\PassRate;

use App\Exceptions\AppException;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\PassRate\ExamTypeLevelPassRate as ExamTypeLevelPassRateAgg;
class ExamTypeLevelPassRate
{
    protected ExamTypeLevelPassRateAgg  $examTypeLevelPassRateAgg;
    public function __construct(ExamTypeLevelPassRateAgg $examTypeLevelPassRateAgg)
    {
        $this->examTypeLevelPassRateAgg = $examTypeLevelPassRateAgg;
    }
    public function getSchoolExamTypeLevelPassRate($currentSchool, $year, array $filters)
    {
        //make the query
        if (($filters['exam_type'] ?? false) && ($filters['level'] ?? false)) {
            throw new AppException(
                 "To fetch exam type pass rate level or exam type must be false and true",
                 409,
                 "Invalid Arguments",
                 "One of the variables in the request must be true or false"
            );
        }
        $targetKpis = [
            AcademicAnalyticsKpi::EXAM_CANDIDATE,
            AcademicAnalyticsKpi::EXAM_PASSED
        ];
        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $targetKpis);


        $defaultFilter = [
            "exam_type" => true,
            "level" => false
        ];

        if(empty($filters)){
             return $this->examTypeLevelPassRateAgg->calculate($query, $defaultFilter);
        }

        if(!empty($filters)){
             return $this->examTypeLevelPassRateAgg->calculate($query, $filters);
        }

        //call filter
        //call aggregate and pass filter
        //return value
    }
}
