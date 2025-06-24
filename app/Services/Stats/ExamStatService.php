<?php

namespace App\Services\Stats;

use App\Models\StatTypes;
use Illuminate\Support\Facades\DB;

class ExamStatService
{
    public function getExamStat($currentSchool, $examId): array
    {
        $kpiNames = [
            'exam_total_students_accessed',
            'exam_total_students_passed',
            'exam_total_students_failed',
            'exam_pass_rate',
            'exam_fail_rate',
            'average_exam_total_score',
            'average_exam_gpa',
            'exam_course_fail_rates',
            'exam_course_pass_rates',
            'exam_course_fail_distribution',
            'exam_course_pass_distribution',
            'exam_course_resit_distribution',
            'exam_total_number_of_resits',
            'exam_grades_distribution',
            'exam_course_score_distribution',
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)
            ->get()
            ->keyBy('program_name');

        $examStatData = DB::table('school_exam_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $examId)
            ->get()
            ->keyBy('stat_type_id');

        $statMap = [
            'exam_total_students_accessed' => ['Number of Students Accessed', 'integer_value'],
            'exam_total_students_passed' => ['Number of Students Passed', 'integer_value'],
            'exam_total_students_failed' => ['Number of Students Failed', 'integer_value'],
            'exam_pass_rate' => ['Exam Pass Rate', 'decimal_value'],
            'exam_fail_rate' => ['Exam Fail Rate', 'decimal_value'],
            'average_exam_total_score' => ['Average Exam Total Score', 'decimal_value'],
            'average_exam_gpa' => ['Average Exam GPA', 'decimal_value'],
            'exam_course_fail_rates' => ['Courses Failed Rate', 'json_value'],
            'exam_course_pass_rates' => ['Courses Pass Rate', 'json_value'],
            'exam_course_fail_distribution' => ['Exam Course Fail Distribution', 'json_value'],
            'exam_course_pass_distribution' => ['Exam Course Pass Distribution', 'json_value'],
            'exam_course_resit_distribution' => ['Exam Course Resit Distribution', 'json_value'],
            'exam_total_number_of_resits' => ['Total Resit Count', 'integer_value'],
            'exam_grades_distribution' => ['Exam Grades Distribution', 'json_value'],
            'exam_course_score_distribution' => ['Exam Grades Distribution By Course', 'json_value'],
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
