<?php

namespace App\Jobs\StatisticalJobs\AcademicJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\StudentResults;
use App\Models\Marks;
use App\Models\StatTypes;
use Illuminate\Support\Str;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CaStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $exam;
    public function __construct($exam)
    {
        $this->exam = $exam;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolBranchId = $this->exam->school_branch_id;
        $schoolYear = $this->exam->school_year;
        $examTypeId = $this->exam->exam_type_id;
        $year = now()->year;
        $month = now()->month;
        $kpiNames = [
            'ca_exam_total_students_accessed',
            'ca_exam_total_students_passed',
            'ca_exam_total_students_failed',
            'ca_exam_pass_rate',
            'ca_exam_fail_rate',
            'average_ca_exam_total_score',
            'average_ca_exam_total_gpa',
            'percentage_increase_ca_pass_rate',
            'percentage_decrease_ca_fail_rate',
            'ca_exam_course_fail_rates',
            'ca_exam_course_pass_rates',
            'ca_exam_course_potential_resit_distribution',
            'ca_total_number_of_potential_resits',
            'ca_exam_grades_distribution',
            'ca_exam_course_score_distribution',
            'ca_exam_course_best_performas',

        ];
        $examResults = StudentResults::where("exam_id", $this->exam->id)->get();
        $studentMarks = Marks::where("exam_id", $this->exam->id)->with('courses')->get();
        $studentResultsHistory = StudentResults::where("specialty_id", $this->exam->specialty_id)
            ->where("level_id", $this->exam->level_id)
            ->with(['exam' => function ($query) {
                $query->where("exam_type_id", $this->exam->exam_type_id);
            }])->get();
    }

      private function prepareStatData(
        ?StatTypes $kpi,
        string $examId,
        string $schoolBranchId,
        string $studentId,
        string $schoolYear,
        int $month,
        int $year,
        string $valueType,
        mixed $value
    ): array {
        $data = [
            'id' => Str::uuid(),
            'exam_id' => $examId,
            'school_branch_id' => $schoolBranchId,
            'student_id' => $studentId,
            // Initialize all value columns to null first
            'decimal_value' => null,
            'integer_value' => null,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'school_year' => $schoolYear,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Assign the actual value to the correct column
        $data[$valueType] = $value;

        return $data;
    }
    public function accessmentStats($examResults)
    {
        $totalStudents = count($examResults);
        $passedStudents = array_filter($examResults, function ($result) {
            return $result['exam_status'] === 'passed';
        });
        $failedStudents = array_filter($examResults, function ($result) {
            return $result['exam_status'] === 'failed';
        });
        return [
            'total_students' => $totalStudents,
            'passed_students' => count($passedStudents),
            'failed_students' => count($failedStudents),
        ];
    }
    public function examPassRate($examResults)
    {
        $totalStudents = count($examResults);
        $passedStudents = array_filter($examResults, function ($result) {
            return $result['exam_status'] === 'passed';
        });
        $totalStudents > 0 ? $passRate = (count($passedStudents) / $totalStudents) * 100 : $passRate = 0;
        return max(0.00, round($passRate, 2));
    }
    public function examFailRate($examResults)
    {
        $totalStudents = count($examResults);
        $failedStudents = array_filter($examResults, function ($result) {
            return $result['exam_status'] === 'failed';
        });
        $totalStudents > 0 ? $failRate = (count($failedStudents) / $totalStudents) * 100 : $failRate = 0;
        return max(0.00, round($failRate, 2));
    }
    public function averageTotalScore($examResults)
    {
        $totalScore = array_reduce($examResults, function ($carry, $result) {
            return $carry + $result['total_score'];
        }, 0);
        $totalStudents = count($examResults);
        $totalStudents > 0 ? $averageScore = $totalScore / $totalStudents : $averageScore = 0;
        return max(0.00, round($averageScore, 2));
    }
    public function averageGpa($examResults)
    {
        $totalGpa = array_reduce($examResults, function ($carry, $result) {
            return $carry + $result['gpa'];
        }, 0);
        $totalStudents = count($examResults);
        $totalStudents > 0 ? $averageGpa = $totalGpa / $totalStudents : $averageGpa = 0;
        return max(0.00, round($averageGpa, 2));
    }
    public function calculateHistoricalPerformanceKpis($currentExamResults, $examResultsHistory)
    {
        $currentPassRate = $this->examPassRate($currentExamResults);
        $currentFailRate = $this->examFailRate($currentExamResults);
        $currentAverageScore = $this->averageTotalScore($currentExamResults);
        $historicalPassRate = $this->examPassRate($examResultsHistory);
        $historicalFailRate = $this->examFailRate($examResultsHistory);
        $historicalAverageScore = $this->averageTotalScore($examResultsHistory);
        $improvementPassRate = $currentPassRate - $historicalPassRate;
        $improvementFailRate = $currentFailRate - $historicalFailRate;
        $kpis = [
            'current_pass_rate' => $currentPassRate,
            'current_fail_rate' => $currentFailRate,
            'improvement_pass_rate' => round($improvementPassRate, 2),
            'improvement_fail_rate' => round($improvementFailRate, 2),
            'current_average_score' => round($currentAverageScore, 2),
            'historical_average_score' => round($historicalAverageScore, 2),
        ];

        return $kpis;
    }
    public function gradesDistributionByExam($studentMarks, $letterGrades){
      $distribution = [];
        foreach ($letterGrades as $gradeDefinition) {
            $letterGrade = $gradeDefinition->letter_grade;
            $distribution[$letterGrade] = [
                'letter_grade' => $letterGrade,
                'count' => 0,
            ];
        }
        foreach ($studentMarks as $mark) {
            $assignedGrade = $mark->grade ?? null;

            if ($assignedGrade && isset($distribution[$assignedGrade])) {
                $distribution[$assignedGrade]['count']++;
            }
        }
        return array_values($distribution);
     }
    public function analyzeCourseStatistics($studentMarks)
    {
        $courseStats = [];
        foreach ($studentMarks as $result) {
            $courseName = $result['course_title'];
            $examStatus = $result['grade_status'];
            if (!isset($courseStats[$courseName])) {
                $courseStats[$courseName] = [
                    'total_students' => 0,
                    'passed_students' => 0,
                    'failed_students' => 0,
                ];
            }
            $courseStats[$courseName]['total_students']++;
            if ($examStatus === 'pass') {
                $courseStats[$courseName]['passed_students']++;
            } else {
                $courseStats[$courseName]['failed_students']++;
            }
        }
        foreach ($courseStats as $courseName => $stats) {
            $total = $stats['total_students'];
            $passRate = $total > 0 ? ($stats['passed_students'] / $total) * 100 : 0;
            $courseStats[$courseName]['pass_rate'] = round($passRate, 2);
        }

        return $courseStats;
    }
    public function analyzeCourseScores($studentMarks)
    {
        $courseScores = [];

        foreach ($studentMarks as $mark) {
            $courseId = $mark->course_id;
            $totalScore = $mark->total_score;
            $courseName = $mark->courses->course_title;
            if (!isset($courseScores[$courseId])) {
                $courseScores[$courseName] = [
                    'highest_score' => $totalScore,
                    'lowest_score' => $totalScore,
                ];
            } else {
                if ($totalScore > $courseScores[$courseId]['highest_score']) {
                    $courseScores[$courseId]['highest_score'] = $totalScore;
                }
                if ($totalScore < $courseScores[$courseId]['lowest_score']) {
                    $courseScores[$courseId]['lowest_score'] = $totalScore;
                }
            }
        }

        return array_values($courseScores);
    }
    public function totalNumberOfPotResits($studentMarks)
    {
        return $studentMarks->where('resit_status', 'high_resit_potential')->count();
    }
    public function courseWithNumberOfPotResits($studentMarks)
    {
        $potResitsPerCourse = [];

        foreach ($studentMarks as $mark) {
            if ($mark->resit_status === 'high_resit_potential') {
                $courseId = $mark->course_id;
                $courseName = $mark->courses->course_title;
                if (!isset($potResitsPerCourse[$courseId])) {
                    $potResitsPerCourse[] = [
                        'course_title' => $courseName,
                        'resit_count' => 1,
                    ];
                } else {
                    $potResitsPerCourse[$courseId]['resit_count']++;
                }
            }
        }

        return array_values($potResitsPerCourse);
    }
    public function coursePassRates($studentMarks)
    {
        $courseData = [];
        foreach ($studentMarks as $mark) {
            $courseId = $mark->course_id;
            $courseName = $mark->courses->course_title;
            if (!isset($courseData[$courseId])) {
                $courseData[$courseId] = [
                    'course_title' => $courseName,
                    'total_students' => 0,
                    'passed_students' => 0,
                ];
            }
            $courseData[$courseId]['total_students']++;
            if ($mark->grade_status === 'pass') {
                $courseData[$courseId]['passed_students']++;
            }
        }
        $passRates = [];
        foreach ($courseData as $courseId => $data) {
            $totalStudents = $data['total_students'];
            $passedStudents = $data['passed_students'];
            $passRate = $totalStudents > 0 ? ($passedStudents / $totalStudents) * 100 : 0;
            $passRates[] = [
                'course_title' => $data['course_title'],
                'pass_rate' => round($passRate, 2),
            ];
        }

        return $passRates;
    }
    public function courseFailRates($studentMarks)
    {
        $courseData = [];
        foreach ($studentMarks as $mark) {
            $courseId = $mark->course_id;
            $courseName = $mark->courses->course_title;
            if (!isset($courseData[$courseId])) {
                $courseData[$courseId] = [
                    'course_title' => $courseName,
                    'total_students' => 0,
                    'fail_students' => 0,
                ];
            }
            $courseData[$courseId]['total_students']++;
            if ($mark->grade_status === 'fail') {
                $courseData[$courseId]['fail_students']++;
            }
        }
        $failRates = [];
        foreach ($courseData as $courseId => $data) {
            $totalStudents = $data['total_students'];
            $failedStudents = $data['passed_students'];
            $failRate = $totalStudents > 0 ? ($failedStudents / $totalStudents) * 100 : 0;
            $failRates[] = [
                'course_title' => $data['course_title'],
                'fail_rate' => round($failRate, 2),
            ];
        }

        return $failRates;
    }
}
