<?php

namespace App\Services\Analytics\Operational\Aggregates\Teacher;

use App\Models\Educationlevels;
use Illuminate\Support\Collection;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;

class TeacherStudentRatioLevel
{
    public function calculate(Collection $enrolllmentQuery, Collection $operationalQuery)
    {
        $levels = Educationlevels::all();
        $levels->map(function ($level) use ($enrolllmentQuery, $operationalQuery) {
            $totalTeacher = $operationalQuery->where("kpi", OperationalAnalyticsKpi::TEACHER_LEVEL_COUNT)
                ->where("level_id", $level->id)->sum("value");
            $totalStudentDropout = $enrolllmentQuery->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)
                ->where("level_id", $level->id)->sum("value");
            $totalStudentEnrolled = $enrolllmentQuery->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)
                ->where("level_id", $level->id)->sum("value");
            return [
                "level_name" => $level->name ?? "unknown",
                "level_number" => $level->level ?? "unknown",
                "total_teachers" => $totalTeacher,
                "total_student_dropout" => $totalStudentDropout,
                "total_student_enrolled" => $totalStudentEnrolled,
                "total_students" => $totalStudentEnrolled - $totalStudentDropout,
                "teacher_student_ratio" => round($totalTeacher / $totalStudentEnrolled - $totalStudentDropout, 2)
            ];
        });
    }
}
