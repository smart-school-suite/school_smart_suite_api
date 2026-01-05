<?php

namespace App\Services\Analytics\Academic\Aggregates\Card;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;
class SchoolExamTotalAggregator
{
    public function calculate(Collection $query){
         return $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM)->sum('value');
    }
}
