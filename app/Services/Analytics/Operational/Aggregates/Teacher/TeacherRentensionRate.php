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

        $retainedTeachers = $registeredTeacher - $teacherDropout;

        $retentionRate = ($registeredTeacher > 0)
            ? ($retainedTeachers / $registeredTeacher) * 100
            : 0;

        return [
            "teacher_dropout" => $teacherDropout,
            "register_teacher" => $registeredTeacher,
            "retained_teacher" => $retainedTeachers,
            "retention_rate" => round($retentionRate, 2)
        ];
    }
}
