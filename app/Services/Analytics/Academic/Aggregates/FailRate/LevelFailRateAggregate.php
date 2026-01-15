<?php

namespace App\Services\Analytics\Academic\Aggregates\FailRate;

use App\Models\Educationlevels;
use Illuminate\Support\Collection;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;

class LevelFailRateAggregate
{
    public static function calculate(Collection $query)
    {
        $levels = Educationlevels::all();

        return $levels->map(function ($level) use ($query) {
            $levelData = $query->where("level_id", $level->id);

            $totalSat = $levelData->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                ->sum("value");

            $totalFailed = $levelData->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_FAILED)
                ->sum("value");

            return [
                "level_id" => $level->id,
                "level_name" => $level->name,
                "level_number" => $level->level,
                "total_sat" => $totalSat,
                "total_failed" => $totalFailed,
                "fail_rate" => self::failRate($totalSat, $totalFailed)
            ];
        });
    }

    protected static function failRate($totalSat, $totalFailed)
    {
        if ($totalSat <= 0) {
            return 0;
        }

        return round(($totalFailed / $totalSat) * 100, 2);
    }
}
