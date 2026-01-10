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
      return   $examTypes->map(function ($examType) use ($query) {
            $totalCandidate = $query->where("kpi", AcademicAnalyticsKpi::EXAM_CANDIDATE)
                ->where("exam_type_id", $examType->id)
                ->sum("value");
            $totalGpa = $query->where("kpi", AcademicAnalyticsKpi::EXAM_GPA)
                ->where("exam_type_id", $examType->id)
                ->sum("value");
            return [
                "exam_type_id" => $examType->id,
                "exam_name" => $examType->exam_name ?? "unknown",
                "semester" => $examType->semester ?? "unknown",
                "total_candidate" => $totalCandidate,
                "total_gpa" => $totalGpa,
                "average_gpa" => round($totalCandidate / $totalGpa * 100, 2)
            ];
        });
    }
}
