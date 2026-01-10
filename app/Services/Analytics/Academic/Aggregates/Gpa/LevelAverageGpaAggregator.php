<?php

namespace App\Services\Analytics\Academic\Aggregates\Gpa;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Models\Educationlevels;
use Illuminate\Support\Collection;

class LevelAverageGpaAggregator
{
    public static function calculate(Collection $query)
    {
        $levels = Educationlevels::all();
        return  $levels->map(function ($level) use ($query) {
            $totalSat = $query->where("kpi", AcademicAnalyticsKpi::EXAM_CANDIDATE)->sum("value");
            $totalGpa = $query->where("kpi", AcademicAnalyticsKpi::EXAM_GPA)->sum("value");
            return [
                "level_id" => $level->id,
                "level_name" => $level->name ?? "unknown",
                "level_number" => $level->level ?? "unknown",
                "total_sat" => $totalSat,
                "total_gpa" => $totalGpa,
                "average_gpa" => round($totalGpa / $totalSat * 100, 2)
            ];
        });
    }
}
