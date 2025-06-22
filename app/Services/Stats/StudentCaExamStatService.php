<?php

namespace App\Services\Stats;

use Illuminate\Support\Facades\DB;
use App\Models\StatTypes;

class StudentCaExamStatService
{
    public function getStudentCaExamStat($currentSchool, $examId, $studentId): array
    {
        $kpiNames = [
            'percentage_increase_performance_by_exam_type',
            'percentage_increase_performance_by_semester',
            'percentage_decrease_performance_by_exam',
            'percentage_decrease_performance_by_semester',
            'courses_sat',
            'courses_passed',
            'courses_failed',
            'pass_rate',
            'fail_rate',
            'school_year_on_gpa_changes_by_exam',
            'school_year_on_total_score_changes_by_exam',
            'potential_resits',
            'chances_of_resit',
            'grades_distribution',
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)
            ->get()
            ->keyBy('program_name');

        $studentExamStatData = DB::table('student_exam_stats')
            ->where("school_branch_id", $currentSchool->id)
            ->where("student_id", $studentId)
            ->where("exam_id", $examId)
            ->get()
            ->keyBy('stat_type_id');

        $statMap = [
            'percentage_increase_performance_by_exam_type' => ['Percentage Improvement By Exam Type', 'decimal_value'],
            'percentage_increase_performance_by_semester' => ['Percentage Improvement By Semester', 'decimal_value'],
            'percentage_decrease_performance_by_exam' => ['Percentage Decrease By Exam Type', 'decimal_value'],
            'percentage_decrease_performance_by_semester' => ['Percentage Decrease By Semester', 'decimal_value'],
            'courses_sat' => ['Courses Sat', 'integer_value'],
            'courses_passed' => ['Courses Passed', 'integer_value'],
            'courses_failed' => ['Courses Failed', 'integer_value'],
            'pass_rate' => ['Pass Rate', 'decimal_value'],
            'fail_rate' => ['Fail Rate', 'decimal_value'],
            'school_year_on_gpa_changes_by_exam' => ['Yearly GPA Changes', 'json_value'],
            'school_year_on_total_score_changes_by_exam' => ['Yearly Total Score Changes', 'json_value'],
            'potential_resits' => ['Exam Potential Resit Count', 'integer_value'],
            'chances_of_resit' => ['Resit Probability', 'decimal_value'],
            'grades_distribution' => ['Grades Distribution', 'json_value'],
        ];

        $results = [];

        foreach ($statMap as $program => [$title, $valueType]) {
            $kpi = $kpis->get($program);
            if (!$kpi) {
                $results[] = ['title' => $title, 'value' => null];
                continue;
            }

            $stat = $studentExamStatData->get($kpi->id);
            if (!$stat) {
                $results[] = ['title' => $title, 'value' => null];
                continue;
            }

            $value = $stat->{$valueType} ?? null;

            if ($valueType === 'json_value') {
                $value = json_decode($value, true);
            }

            $results[] = [
                'title' => $title,
                'value' => $value
            ];
        }

        return $results;
    }
}
