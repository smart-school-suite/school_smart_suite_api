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

        return $levels->map(function ($level) use ($query) {
            $levelData = $query->where("level_id", $level->id);

            $totalSat = $levelData->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                ->sum("value");

            $totalGpa = $levelData->where("kpi", AcademicAnalyticsKpi::SCHOOL_GPA)
                ->sum("value");

            $averageGpa = ($totalSat > 0)
                ? ($totalGpa / $totalSat)
                : 0;

            return [
                "level_id" => $level->id,
                "level_name" => $level->name ?? "unknown",
                "level_number" => $level->level ?? "unknown",
                "total_sat" => $totalSat,
                "total_gpa" => $totalGpa,
                "average_gpa" => round($averageGpa, 2)
            ];
        });
    }
}
