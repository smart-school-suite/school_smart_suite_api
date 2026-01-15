<?php

namespace App\Services\Analytics\Academic\Aggregates\PassRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Models\Educationlevels;
use Illuminate\Support\Collection;

class LevelPassRateAggregate
{
    public static function calculate(Collection $query)
    {
        $levels = Educationlevels::all();

        return $levels->map(function ($level) use ($query) {
            $levelData = $query->where("level_id", $level->id);

            $totalCandidate = $levelData->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                ->sum("value");

            $totalPassed = $levelData->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_PASSED)
                ->sum("value");

            $passRate = ($totalCandidate > 0)
                ? ($totalPassed / $totalCandidate) * 100
                : 0;

            return [
                "level_id" => $level->id,
                "level_name" => $level->name,
                "level_number" => $level->level,
                "total_candidate" => $totalCandidate,
                "total_passed" => $totalPassed,
                "pass_rate" => round($passRate, 2)
            ];
        });
    }
}
