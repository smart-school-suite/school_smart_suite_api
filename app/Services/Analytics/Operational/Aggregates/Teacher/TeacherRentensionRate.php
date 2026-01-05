<?php

namespace App\Services\Analytics\Operational\Aggregates\Teacher;

use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use Illuminate\Support\Collection;

class TeacherRentensionRate
{
    public function calculate(Collection $query)
    {
        $teacherDropout = $query->where("kpi", OperationalAnalyticsKpi::TEACHER_DROPOUT)
            ->sum("value");
        $registeredTeacher = $query->where("kpi", OperationalAnalyticsKpi::TEACHER)
            ->sum("value");
        return  round($teacherDropout / $registeredTeacher * 100, 2);
    }
}
