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
        return  $levels->map(function ($level) use ($query) {
            $totalSat = $query->where("level_id", $level->id)
                ->where("kpi", AcademicAnalyticsKpi::EXAM_CANDIDATE)
                ->sum("value");
            $totalPassed = $query->where("level_id", $level->id)
                ->where("kpi", AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_FAILED)
                ->sum("value");
            return [
                "level_id" => $level->id,
                "level_name" => $level->name,
                "level_number" => $level->level,
                "total_sat" => $totalSat,
                "total_passed" => $totalPassed,
                "fail_rate" => self::failRate($totalSat, $totalPassed)
            ];
        });
    }

    protected static function failRate($totalSat, $totalFailed)
    {
        return round($totalFailed / $totalSat * 100);
    }
}
