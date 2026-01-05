<?php

namespace App\Services\Analytics\Academic\Aggregates\PassRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolPassRateAggregate
{
    public function calculate(Collection $query, $filters)
    {
        if (!$filters['level_id'] && !$filters['exam_type_id']) {
            $totalSat =   $query->where('kpi', AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                ->sum('value');
            $totalPassed = $query->where("kpi", AcademicAnalyticsKpi::EXAM_PASSED)
                ->sum('value');
            return $this->rate($totalPassed, $totalSat);
        }
        if (!$filters['level_id'] && $filters['exam_type_id']) {
            $totalSat = $query->where('kpi', AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                ->sum('value');
            $totalPassed = $query->where("kpi", AcademicAnalyticsKpi::EXAM_PASSED)
                ->sum('value');
            return $this->rate($totalPassed, $totalSat);
        }
        if (!$filters['exam_type_id'] && $filters['level_id']) {
            $totalSat = $query->where('kpi', AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                ->sum('value');
            $totalPassed = $query->where("kpi", AcademicAnalyticsKpi::EXAM_PASSED)
                ->sum('value');
        }
        if($filters['exam_type_id'] && $filters['level_id']){
            $totalSat = $query->where('kpi', AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                ->sum('value');
            $totalPassed = $query->where("kpi", AcademicAnalyticsKpi::EXAM_PASSED)
                ->sum('value');
        }
    }

    protected function rate($passed, $total): float
    {
        if ($total == 0) {
            return 0.0;
        }

        return round(($passed / $total) * 100, 2);
    }
}
