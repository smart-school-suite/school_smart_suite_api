<?php

namespace App\Services\Analytics\Academic\Widget\Card;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;
use App\Services\Analytics\Academic\Aggregates\Card\SchoolAverageGpaAggregator;
use App\Services\Analytics\Academic\Aggregates\Card\SchoolExamCandidateTotalAggregator;
use App\Services\Analytics\Academic\Aggregates\Card\SchoolExamTotalAggregator;

class CardStats
{
    protected SchoolAverageGpaAggregator $schoolAverageGpaAggregator;
    protected SchoolExamCandidateTotalAggregator $schoolExamCandidateTotalAggregator;
    protected SchoolExamTotalAggregator $schoolExamTotalAggregator;
    public function getCardStats($currentSchool, $year)
    {
        $targetKpis = [
            AcademicAnalyticsKpi::SCHOOL_EXAM,
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE,
            AcademicAnalyticsKpi::SCHOOL_GPA
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return [
            "school_average_gpa" => $this->schoolAverageGpaAggregator->calculate($query),
            "total_evaluated_candidate" => $this->schoolExamCandidateTotalAggregator->calculate($query),
            "total_exams" => $this->schoolExamTotalAggregator->calculate($query)
        ];
    }
}
