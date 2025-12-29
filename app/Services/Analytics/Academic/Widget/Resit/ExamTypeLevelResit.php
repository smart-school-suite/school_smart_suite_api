<?php

namespace App\Services\Analytics\Academic\Widget\Resit;

use App\Exceptions\AppException;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;
use App\Services\Analytics\Academic\Aggregates\Resit\ResitTotalAggregate;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;

class ExamTypeLevelResit
{
    protected ResitTotalAggregate $resitTotalAggregate;
    public function __construct(ResitTotalAggregate $resitTotalAggregate)
    {
        $this->resitTotalAggregate = $resitTotalAggregate;
    }
    public function getExamTypeLevelResitCount($currentSchool, $year, $filters)
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
            AcademicAnalyticsKpi::RESIT
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $targetKpis);

        $defaultFilter = [
            "exam_type" => true,
            "level" => false
        ];

        if (empty($filters)) {
            return $this->resitTotalAggregate->calculate($query, $defaultFilter);
        }

        if (!empty($filters)) {
            return $this->resitTotalAggregate->calculate($query, $filters);
        }
    }
}
