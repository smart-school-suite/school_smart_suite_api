<?php

namespace App\Services\Stats;

use App\Models\StatTypes;
use Illuminate\Support\Facades\DB;

class CaExamStatService
{
    public function getCaExamStats($currentSchool, $examId): array
    {
        $kpiNames = [
            'ca_exam_total_students_accessed',
            'ca_exam_total_students_passed',
            'ca_exam_total_students_failed',
            'ca_exam_pass_rate',
            'ca_exam_fail_rate',
            'average_ca_exam_total_score',
            'average_ca_exam_gpa',
            'ca_exam_course_fail_rates',
            'ca_exam_course_pass_rates',
            'ca_exam_course_fail_distribution',
            'ca_exam_course_pass_distribution',
            'ca_exam_course_potential_resit_distribution',
            'ca_total_number_of_potential_resits',
            'ca_exam_grades_distribution',
            'ca_exam_course_score_distribution',
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)
            ->get()
            ->keyBy('program_name');

        $examStatData = DB::table('school_ca_exam_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $examId)
            ->get()
            ->keyBy('stat_type_id');

        $statMap = [
            'ca_exam_total_students_accessed' => ['Number of Students Accessed', 'integer_value'],
            'ca_exam_total_students_passed' => ['Number of Students Passed', 'integer_value'],
            'ca_exam_total_students_failed' => ['Number of Students Failed', 'integer_value'],
            'ca_exam_pass_rate' => ['Exam Pass Rate', 'decimal_value'],
            'ca_exam_fail_rate' => ['Exam Fail Rate', 'decimal_value'],
            'average_ca_exam_total_score' => ['Average Exam Total Score', 'decimal_value'],
            'average_ca_exam_gpa' => ['Average Exam GPA', 'decimal_value'],
            'ca_exam_course_fail_rates' => ['Courses Fail Rate', 'json_value'],
            'ca_exam_course_pass_rates' => ['Courses Pass Rate', 'json_value'],
            'ca_exam_course_fail_distribution' => ['Exam Course Fail Distribution', 'json_value'],
            'ca_exam_course_pass_distribution' => ['Exam Course Pass Distribution', 'json_value'],
            'ca_exam_course_potential_resit_distribution' => ['Exam Course Potential Resit Distribution', 'json_value'],
            'ca_total_number_of_potential_resits' => ['Total Potential Resit Count', 'integer_value'],
            'ca_exam_grades_distribution' => ['Exam Grades Distribution', 'json_value'],
            'ca_exam_course_score_distribution' => ['Exam Grades Distribution By Course', 'json_value'],
        ];

        $results = [];

        foreach ($statMap as $program => [$title, $valueType]) {
            $kpi = $kpis->get($program);
            if (!$kpi) {
                $results[] = ['title' => $title, 'value' => null];
                continue;
            }

            $stat = $examStatData->get($kpi->id);
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
