<?php

namespace App\Services\Analytics\Academic\Widget\FailRate;

use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Exceptions\AppException;
use App\Services\Analytics\Academic\Aggregates\FailRate\SchoolFailRateAggregate;

class ExamTypeLevelFailRate
{
    protected SchoolFailRateAggregate $schoolFailRateAggregate;
    public function __construct(SchoolFailRateAggregate $schoolFailRateAggregate)
    {
        $this->schoolFailRateAggregate = $schoolFailRateAggregate;
    }

    public function getSchoolExamTypeLevelFailRate($currentSchool, $year, $filter)
    {
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
            AcademicAnalyticsKpi::EXAM_FAILED
        ];
        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $targetKpis);


        $defaultFilter = [
            "exam_type" => true,
            "level" => false
        ];

        if (empty($filters)) {
            return $this->schoolFailRateAggregate->calculate($query, $defaultFilter);
        }

        if (!empty($filters)) {
            return $this->schoolFailRateAggregate->calculate($query, $filters);
        }
    }
}
