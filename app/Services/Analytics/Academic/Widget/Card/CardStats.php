<?php

namespace App\Services\Analytics\Academic\Widget\Card;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;
use App\Services\Analytics\Academic\Aggregates\Card\SchoolResitTotalAggregate;
use App\Services\Analytics\Academic\Aggregates\Card\SchoolExamCandidateTotalAggregator;
use App\Services\Analytics\Academic\Aggregates\Card\SchoolExamTotalAggregator;

class CardStats
{
    protected SchoolExamCandidateTotalAggregator $schoolExamCandidateTotalAggregator;
    protected SchoolExamTotalAggregator $schoolExamTotalAggregator;
    protected SchoolResitTotalAggregate $schoolResitTotalAggregate;
    public function  __construct(
        SchoolExamCandidateTotalAggregator $schoolExamCandidateTotalAggregator,
        SchoolExamTotalAggregator $schoolExamTotalAggregator,
        SchoolResitTotalAggregate $schoolResitTotalAggregate
    ) {
        $this->schoolExamCandidateTotalAggregator = $schoolExamCandidateTotalAggregator;
        $this->schoolExamTotalAggregator = $schoolExamTotalAggregator;
        $this->schoolResitTotalAggregate = $schoolResitTotalAggregate;
    }
    public function getCardStats($currentSchool, $year)
    {
        $targetKpis = [
            AcademicAnalyticsKpi::SCHOOL_EXAM,
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE,
            AcademicAnalyticsKpi::SCHOOL_RESIT_TOTAL
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return [
            "total_resits" => $this->schoolResitTotalAggregate->calculate($query),
            "total_evaluated_candidate" => $this->schoolExamCandidateTotalAggregator->calculate($query),
            "total_exams" => $this->schoolExamTotalAggregator->calculate($query)
        ];
    }
}
