<?php

namespace App\Services\Stats;

use Illuminate\Support\Facades\DB;
use App\Models\StatTypes;
use Illuminate\Support\Collection;

class StudentExamStatService
{
    public function getStudentExamStat($currentSchool, $examId, $studentId): array
    {
        $kpiNames = [
            'student_exam_percentage_increase_performance_by_exam_type',
            'student_exam_percentage_increase_performance_by_semester',
            'student_exam_percentage_decrease_performance_by_exam_type',
            'student_exam_percentage_decrease_performance_by_semester',
            'student_exam_courses_sat',
            'student_exam_courses_passed',
            'student_exam_courses_failed',
            'student_exam_pass_rate',
            'student_exam_fail_rate',
            'student_exam_school_year_on_gpa_changes_by_exam',
            'student_exam_school_year_on_total_score_changes_by_exam',
            'student_exam_resits',
            'student_exam_no_resit',
            'student_exam_grades_distribution',
            'student_exam_marks_score_distribution_by_course'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)
            ->get()
            ->keyBy('program_name');

        $studentExamStatData = DB::table('student_exam_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->get()
            ->keyBy('stat_type_id');

        $result = [];

        $statMap = [
            'student_exam_percentage_increase_performance_by_exam_type' => ['Percentage Improvement By Exam Type', 'decimal_value'],
            'student_exam_percentage_increase_performance_by_semester' => ['Percentage Improvement By Semester', 'decimal_value'],
            'student_exam_percentage_decrease_performance_by_exam_type' => ['Percentage Decrease By Exam Type', 'decimal_value'],
            'student_exam_percentage_decrease_performance_by_semester' => ['Percentage Decrease By Semester', 'decimal_value'],
            'student_exam_courses_sat' => ['Courses Sat', 'integer_value'],
            'student_exam_courses_passed' => ['Courses Passed', 'integer_value'],
            'student_exam_courses_failed' => ['Courses Failed', 'integer_value'],
            'student_exam_pass_rate' => ['Pass Rate', 'decimal_value'],
            'student_exam_fail_rate' => ['Fail Rate', 'decimal_value'],
            'student_exam_school_year_on_gpa_changes_by_exam' => ['Yearly GPA Changes', 'json_value'],
            'student_exam_school_year_on_total_score_changes_by_exam' => ['Yearly Total Score Changes', 'json_value'],
            'student_exam_resits' => ['Exam Resit Count', 'integer_value'],
            'student_exam_grades_distribution' => ['Grades Distribution', 'json_value'],
            'student_exam_marks_score_distribution_by_course' => ['Marks Distribution By Course', 'json_value'],
        ];

        foreach ($statMap as $program => [$title, $valueType]) {
            $kpi = $kpis->get($program);
            if (!$kpi) {
                $result[] = ['title' => $title, 'value' => null];
                continue;
            }

            $stat = $studentExamStatData->get($kpi->id);
            if (!$stat) {
                $result[] = ['title' => $title, 'value' => null];
                continue;
            }

            $value = $stat->{$valueType} ?? null;

            // If JSON, decode it
            if ($valueType === 'json_value') {
                $value = json_decode($value, true);
            }

            $result[] = [
                'title' => $title,
                'value' => $value
            ];
        }

        return $result;
    }
}
