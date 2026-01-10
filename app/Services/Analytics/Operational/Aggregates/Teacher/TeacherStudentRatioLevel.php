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

        return $levels->map(function ($level) use ($enrolllmentQuery, $operationalQuery) {
            $levelEnrollment = $enrolllmentQuery->where("level_id", $level->id);

            $totalTeacher = $operationalQuery->where("kpi", OperationalAnalyticsKpi::TEACHER_LEVEL_COUNT)
                ->where("level_id", $level->id)
                ->sum("value");

            $totalStudentDropout = $levelEnrollment->where("kpi", EnrollmentAnalyticsKpi::STUDENT_DROPOUT)
                ->sum("value");

            $totalStudentEnrolled = $levelEnrollment->where("kpi", EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS)
                ->sum("value");

            $totalStudents = $totalStudentEnrolled - $totalStudentDropout;

            $ratio = ($totalStudents > 0)
                ? ($totalTeacher / $totalStudents)
                : 0;

            return [
                "level_name" => $level->name ?? "unknown",
                "level_number" => $level->level ?? "unknown",
                "total_teachers" => $totalTeacher,
                "total_student_dropout" => $totalStudentDropout,
                "total_student_enrolled" => $totalStudentEnrolled,
                "total_students" => $totalStudents,
                "teacher_student_ratio" => round($ratio, 2)
            ];
        });
    }
}
