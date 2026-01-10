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
            $totalCandidate = $query->where("level_id", $level->id)
                ->where("kpi", AcademicAnalyticsKpi::EXAM_CANDIDATE)
                ->sum("value");
            $totalPassed = $query->where("level_id", $level->id)
                ->where("kpi", AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_PASSED)
                ->sum("value");
            return [
                "level_id" => $level->id,
                "level_name" => $level->name,
                "level_number" => $level->level,
                "total_candidate" => $totalCandidate,
                "total_passed" => $totalPassed,
                "pass_rate" => round($totalPassed / $totalCandidate * 100, 2)
            ];
        });
    }
}
