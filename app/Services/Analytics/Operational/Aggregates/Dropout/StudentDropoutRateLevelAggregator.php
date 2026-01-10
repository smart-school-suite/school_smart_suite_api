<?php

namespace App\Services\Analytics\Operational\Aggregates\Dropout;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Models\Educationlevels;
use Illuminate\Support\Collection;

class StudentDropoutRateLevelAggregator
{
    public function calculate(Collection $query)
    {
        $levels = Educationlevels::all();
       return   $levels->map(function ($level) use ($query) {
            $enrollments = (clone $query)
                ->where("level_id", $level->id)
                ->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)
                ->sum("value");
            $totalDropout = (clone $query)
                ->where("level_id", $level->id)
                ->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)
                ->sum("value");
            return [
                "level_id" => $level->id,
                "level" => $level->name ?? "unknown",
                "level_number" => $level->level ?? "unknown",
                "total_enrollment" => $enrollments ?? 0,
                "total_dropout" => $totalDropout ?? 0,
                "dropout_rate" => $this->rate($enrollments, $totalDropout)
            ];
        });
    }
    protected function rate($enrollments, $totalDropout): float
    {
        if ($totalDropout == 0) {
            return 0.0;
        }

        return round(($totalDropout / $enrollments) * 100, 2);
    }
}
