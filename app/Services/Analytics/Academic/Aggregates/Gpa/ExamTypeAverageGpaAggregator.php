<?php

namespace App\Services\Analytics\Academic\Aggregates\Gpa;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Models\Examtype;
use Illuminate\Support\Collection;

class ExamTypeAverageGpaAggregator
{
    public static function calculate(Collection $query)
    {
        $examTypes = Examtype::all();

        return $examTypes->map(function ($examType) use ($query) {
            $examData = $query->where("exam_type_id", $examType->id);

            $totalCandidate = $examData->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                ->sum("value");

            $totalGpa = $examData->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                ->sum("value");

            $averageGpa = ($totalCandidate > 0)
                ? ($totalGpa / $totalCandidate)
                : 0;

            return [
                "exam_type_id" => $examType->id,
                "exam_name" => $examType->exam_name ?? "unknown",
                "semester" => $examType->semester ?? "unknown",
                "total_candidate" => $totalCandidate,
                "total_gpa" => $totalGpa,
                "average_gpa" => round($averageGpa, 2)
            ];
        });
    }
}
