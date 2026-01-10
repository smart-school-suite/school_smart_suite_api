<?php

namespace App\Services\Analytics\Operational\Aggregates\Retention;

use App\Models\Educationlevels;
use Illuminate\Support\Collection;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;

class StudentLevelRetentionRateAggregate
{
    public function calculate(Collection $query)
    {
        $levels = Educationlevels::all();

        return $levels->map(function ($level) use ($query) {
            $levelData = $query->where("level_id", $level->id);

            $enrolledStudent = $levelData->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)->sum("value");
            $dropoutStudent = $levelData->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)->sum("value");

            $retainedStudent = $enrolledStudent - $dropoutStudent;

            $retentionRate = ($enrolledStudent > 0)
                ? ($retainedStudent / $enrolledStudent) * 100
                : 0;

            return [
                "level_name"       => $level->name ?? "unknown",
                "level_number"     => $level->level ?? "unknown",
                "enrolled_student" => $enrolledStudent,
                "dropout_student"  => $dropoutStudent,
                "retained_student" => $retainedStudent,
                "retention_rate"   => round($retentionRate, 2)
            ];
        });
    }
}
