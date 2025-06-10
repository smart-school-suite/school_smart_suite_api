<?php

namespace App\Jobs\StatisticalJobs\AcademicJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\StudentResults;
use App\Models\Marks;
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
        $examResults = StudentResults::where("exam_id", $this->exam->id)->get();
        $studentMarks = Marks::where("exam_id", $this->exam->id)->with('courses')->get();
        $studentResultsHistory = StudentResults::where("specialty_id", $this->exam->specialty_id)
            ->where("level_id", $this->exam->level_id)
            ->with(['exam' => function ($query) {
                $query->where("exam_type_id", $this->exam->exam_type_id);
            }])->get();
    }

    public function accessmentStats($examResults)
    {
        $totalStudents = count($examResults);
        $passedStudents = array_filter($examResults, function ($result) {
            return $result['exam_status'] === 'pass';
        });
        $failedStudents = array_filter($examResults, function ($result) {
            return $result['exam_status'] === 'fail';
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
            return $result['exam_status'] === 'pass';
        });
        $totalStudents > 0 ? $passRate = (count($passedStudents) / $totalStudents) * 100 : $passRate = 0;
        return max(0.00, round($passRate, 2));
    }
    public function examFailRate($examResults)
    {
        $totalStudents = count($examResults);
        $failedStudents = array_filter($examResults, function ($result) {
            return $result['exam_status'] === 'fail';
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
    public function analyzeGpaDistribution($currentExamResults)
    {
        $gpas = array_map(function ($result) {
            return $result['gpa'];
        }, $currentExamResults);
        sort($gpas);
        $totalStudents = count($gpas);
        $top10Index = ceil($totalStudents * 0.90) - 1;
        $bottom10Index = floor($totalStudents * 0.10) - 1;
        $top10Group = array_slice($gpas, $top10Index);
        $bottom10Group = array_slice($gpas, 0, $bottom10Index + 1);
        $topStats = $this->calculateStatistics($top10Group);
        $bottomStats = $this->calculateStatistics($bottom10Group);
        return [
            'top_10' => array_merge($topStats, ['gpas' => $top10Group]),
            'bottom_10' => array_merge($bottomStats, ['gpas' => $bottom10Group]),
        ];
    }
    private function calculateStatistics($gpas)
    {
        $count = count($gpas);
        if ($count === 0) return [
            'count' => 0,
            'mean' => 0,
            'median' => 0,
            'range' => 0,
            'standard_deviation' => 0,
        ];

        $mean = array_sum($gpas) / $count;
        $median = $this->calculateMedian($gpas);
        $range = max($gpas) - min($gpas);
        $stdDeviation = $this->calculateStandardDeviation($gpas, $mean);

        return [
            'count' => $count,
            'mean' => round($mean, 2),
            'median' => round($median, 2),
            'range' => round($range, 2),
            'standard_deviation' => round($stdDeviation, 2),
        ];
    }
    private function calculateMedian($gpas)
    {
        $count = count($gpas);
        $middle = floor(($count - 1) / 2);
        if ($count % 2) {
            return $gpas[$middle]; // odd count
        } else {
            return ($gpas[$middle] + $gpas[$middle + 1]) / 2.0; // even count
        }
    }
    private function calculateStandardDeviation($gpas, $mean)
    {
        $sumOfSquares = array_reduce($gpas, function ($carry, $gpa) use ($mean) {
            return $carry + pow($gpa - $mean, 2);
        }, 0);
        return sqrt($sumOfSquares / count($gpas));
    }
    public function analyzeCourseStatistics($studentMarks)
    {
        $courseStats = [];
        foreach ($studentMarks as $result) {
            $courseName = $result['course_name'];
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
            $courseName = $mark->courses->name;
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
                $courseName = $mark->courses->name;
                if (!isset($potResitsPerCourse[$courseId])) {
                    $potResitsPerCourse[] = [
                        'course_name' => $courseName,
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
            $courseName = $mark->courses->name;
            if (!isset($courseData[$courseId])) {
                $courseData[$courseId] = [
                    'course_name' => $courseName,
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
                'course_name' => $data['course_name'],
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
            $courseName = $mark->courses->name;
            if (!isset($courseData[$courseId])) {
                $courseData[$courseId] = [
                    'course_name' => $courseName,
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
                'course_name' => $data['course_name'],
                'fail_rate' => round($failRate, 2),
            ];
        }

        return $failRates;
    }
}
