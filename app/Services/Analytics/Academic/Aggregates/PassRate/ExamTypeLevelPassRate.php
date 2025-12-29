<?php

namespace App\Services\Analytics\Academic\Aggregates\PassRate;

use Illuminate\Database\Query\Builder;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Models\Examtype;
use App\Models\Educationlevels;
class ExamTypeLevelPassRate
{
    public function calculate(Builder $query, $filters)
    {
        if ($filters['exam_type'] ?? false) {
            return $this->byExamType($query);
        }

        if ($filters['level'] ?? false) {
            return $this->byLevel($query);
        }
    }

    protected function byExamType(Builder $query)
    {
        $examTypes = ExamType::where("type", "!=", "resit")->pluck('id');

        return $examTypes->map(function ($examTypeId) use ($query) {

            $totalSat = (clone $query)
                ->where('exam_type_id', $examTypeId)
                ->where('kpi', AcademicAnalyticsKpi::EXAM_CANDIDATE)
                ->sum('value');

            $totalPassed = (clone $query)
                ->where('exam_type_id', $examTypeId)
                ->where('kpi', AcademicAnalyticsKpi::EXAM_PASSED)
                ->sum('value');

            return [
                'exam_type_id' => Examtype::find($examTypeId)->exam_name ?? "unknown",
                'pass_rate'    => $this->rate($totalPassed, $totalSat),
            ];
        });
    }

    protected function byLevel(Builder $query)
    {
        $levels = Educationlevels::all()->pluck('id');
        return $levels->map(function ($levelId) use ($query) {

            $totalSat = (clone $query)
                ->where('level_id', $levelId)
                ->where('kpi', AcademicAnalyticsKpi::EXAM_CANDIDATE)
                ->sum('value');

            $totalPassed = (clone $query)
                ->where('level_id', $levelId)
                ->where('kpi', AcademicAnalyticsKpi::EXAM_PASSED)
                ->sum('value');

            return [
                'level_id'  => $levelId,
                'pass_rate' => $this->rate($totalPassed, $totalSat),
            ];
        });
    }

    protected function rate($passed, $total): float
    {
        if ($total == 0) {
            return 0.0;
        }

        return round(($passed / $total) * 100, 2);
    }
}
