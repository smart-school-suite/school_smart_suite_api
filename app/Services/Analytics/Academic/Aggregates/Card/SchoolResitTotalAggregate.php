<?php

namespace App\Services\Analytics\Academic\Aggregates\Card;

use Illuminate\Support\Collection;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;

class SchoolResitTotalAggregate
{
    public static function calculate(Collection $query)
    {
        return $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_RESIT_TOTAL)->sum("value") ?? 0;
    }
}
