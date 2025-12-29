<?php

namespace App\Services\Analytics\Academic\Aggregates\Card;


use Illuminate\Database\Query\Builder;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
class SchoolExamCandidateTotalAggregator
{
    public function calculate(Builder $query){
         return $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)->sum('value');
    }
}
