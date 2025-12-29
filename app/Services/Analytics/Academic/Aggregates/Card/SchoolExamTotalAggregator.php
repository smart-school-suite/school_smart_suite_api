<?php

namespace App\Services\Analytics\Academic\Aggregates\Card;
use Illuminate\Database\Query\Builder;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
class SchoolExamTotalAggregator
{
    public function calculate(Builder $query){
         return $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM)->sum('value');
    }
}
