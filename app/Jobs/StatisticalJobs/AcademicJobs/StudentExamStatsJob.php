<?php

namespace App\Jobs\StatisticalJobs\AcademicJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Marks;
use App\Models\Exams;
use App\Models\Student;
use App\Models\StudentResults;

class StudentExamStatsJob implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $exam;
    protected $student;

    /**
     * Create a new job instance.
     *
     * @param  $exam The ID of the exam for which scores were submitted.
     * @param  $student The ID of the student.
     */
    public function __construct(Exams $exam, Student $student)
    {
        $this->exam = $exam;
        $this->student = $student;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $currentExamResults = StudentResults::where('student_id', $this->student->Id)
            ->where('exam_id', $this->exam->Id)
            ->with(['exam', 'exam.semester', 'exam.examType'])
            ->first();

        $previousExamResults = StudentResults::where('student_id', $this->student->Id)
            ->where('exam_id', '<>', $this->exam->Id)
            ->where("exam_type_id", $currentExamResults->exam_type_id)
            ->with(['exam', 'exam.semester', 'exam.examType'])
            ->get();
        $classExamResults = StudentResults::where('specialty_id', $this->student->specialty_id)
            ->where("level_id", $this->student->level_id)
            ->where("student_batch_id", $this->student->student_batch_id)
            ->where('exam_id', $this->exam->Id)
            ->with(['exam', 'exam.semester', 'exam.examType'])
            ->get();
        $marks = Marks::where('student_id', $this->student->id)
            ->where('exam_id', $this->exam->id)
            ->with(['exam', 'exam.semester', 'exam.examType'])
            ->get();

        $this->increaseInPerformanceByExamType($currentExamResults, $previousExamResults);
    }

    public function increaseInPerformanceByExamType($currentExamResult, $previousExamResults)
    {
        if (!$currentExamResult) {
            return 0.0;
        }
        $currentExamTypeId = $currentExamResult->exam->examType->id;

        $filteredPreviousResults = $previousExamResults->filter(function ($previousResult) use ($currentExamTypeId) {
            return $previousResult->exam->examType->id === $currentExamTypeId;
        });

        $currentScore = $currentExamResult->gpa;
        if ($filteredPreviousResults->isEmpty()) {
            return 100.00;
        }

        $previousAverageGpa = $filteredPreviousResults->avg('gpa');

        if ($previousAverageGpa > 0) {
            $percentageIncrease = (($currentScore - $previousAverageGpa) / $previousAverageGpa) * 100;
        } else {
            $percentageIncrease = 100;
        }
        return max(0.00, round($percentageIncrease, 2));
    }
    public function increaseInPerformanceBySemester($currentExamResult, $previousExamResults)
    {
        if (!$currentExamResult) {
            return 0.0;
        }
        $currentExamSemeserId = $currentExamResult->exam->semester->id;

        $filteredPreviousResults = $previousExamResults->filter(function ($previousResult) use ($currentExamSemeserId) {
            return $previousResult->exam->examType->id === $currentExamSemeserId;
        });

        $currentScore = $currentExamResult->gpa;
        if ($filteredPreviousResults->isEmpty()) {
            return 100.00;
        }

        $previousAverageGpa = $filteredPreviousResults->avg('gpa');

        if ($previousAverageGpa > 0) {
            $percentageIncrease = (($currentScore - $previousAverageGpa) / $previousAverageGpa) * 100;
        } else {
            $percentageIncrease = 100;
        }
        return max(0.00, round($percentageIncrease, 2));
    }
    public function decreaseInPerformanceByExamType($currentExamResult, $previousExamResults)
    {
        if (!$currentExamResult) {
            return 0.0;
        }

        $currentExamTypeId = $currentExamResult->exam->examType->id;
        $filteredPreviousResults = $previousExamResults->filter(function ($previousResult) use ($currentExamTypeId) {
            return $previousResult->exam->examType->id === $currentExamTypeId;
        });

        $currentScore = $currentExamResult->gpa;

        if ($filteredPreviousResults->isEmpty()) {
            return 100.00;
        }

        $previousAverageGpa = $filteredPreviousResults->avg('gpa');

        if ($currentScore > $previousAverageGpa) {
            $percentageChange = (($currentScore - $previousAverageGpa) / $previousAverageGpa) * 100;
        } elseif ($currentScore < $previousAverageGpa) {
            $percentageChange = - ((($previousAverageGpa - $currentScore) / $previousAverageGpa) * 100);
        } else {
            return 0.00;
        }
        return max(0.00, round($percentageChange, 2));
    }
    public function decreaseInPerformanceBySemester($currentExamResult, $previousExamResults)
    {
        if (!$currentExamResult) {
            return 0.0;
        }

        $currentExamSemeserId = $currentExamResult->exam->semester->id;
        $filteredPreviousResults = $previousExamResults->filter(function ($previousResult) use ($currentExamSemeserId) {
            return $previousResult->exam->examType->id === $currentExamSemeserId;
        });

        $currentScore = $currentExamResult->gpa;

        if ($filteredPreviousResults->isEmpty()) {
            return 100.00;
        }

        $previousAverageGpa = $filteredPreviousResults->avg('gpa');

        if ($currentScore > $previousAverageGpa) {
            $percentageChange = (($currentScore - $previousAverageGpa) / $previousAverageGpa) * 100;
        } elseif ($currentScore < $previousAverageGpa) {
            $percentageChange = - ((($previousAverageGpa - $currentScore) / $previousAverageGpa) * 100);
        } else {
            return 0.00;
        }
        return max(0.00, round($percentageChange, 2));
    }
    public function groupStudentGpaByExamTypeAndSemester($currentExamResult, $previousExamResults)
    {
        $gpaBySemesterAndType = [];
        $allResults = [];
        if ($currentExamResult) {
            $allResults[] = $currentExamResult;
        }
        foreach ($previousExamResults as $previousResult) {
            $allResults[] = $previousResult;
        }

        foreach ($allResults as $result) {
            $examSemester = $result->exam->semester->name;
            $examType = $result->exam->examType->name;
            $gpa = $result->gpa;
            $type = $result === $currentExamResult ? 'current' : 'previous';
            $gpaBySemesterAndType[$examSemester][$examType][$type][] = $gpa;
        }
        return $gpaBySemesterAndType;
    }

    public function groupStudentTotalScoreByExamTypeAndSemester($currentExamResult, $previousExamResults)
    {
        $gpaBySemesterAndType = [];
        $allResults = [];
        if ($currentExamResult) {
            $allResults[] = $currentExamResult;
        }
        foreach ($previousExamResults as $previousResult) {
            $allResults[] = $previousResult;
        }

        foreach ($allResults as $result) {
            $examSemester = $result->exam->semester->name;
            $examType = $result->exam->examType->name;
            $total_score = $result->total_score;
            $type = $result === $currentExamResult ? 'current' : 'previous';
            $gpaBySemesterAndType[$examSemester][$examType][$type][] = $total_score;
        }
        return $gpaBySemesterAndType;
    }
    public function coursesSat($marks)
    {
        return $marks->count();
    }
    public function coursesPassed($marks)
    {
        return $marks->where('status', 'pass')->count();
    }
    public function coursesFailed($marks)
    {
        return $marks->where('status', 'fail')->count();
    }
    public function classAveragePerformanceVsIndividualPerformance($classExamResults, $currentExamResult)
    {
        $classAverage = array_sum(array_column($classExamResults, 'gpa')) / count($classExamResults);

        $performanceDeviation = $currentExamResult->gpa - $classAverage;

        $aboveAverageCount = count(array_filter($classExamResults, function ($student) use ($currentExamResult) {
            return $student->gpa > $currentExamResult->gpa;
        }));

        $percentageAboveAverage = ($aboveAverageCount / count($classExamResults)) * 100;

        $squaredDiffs = array_map(function ($student) use ($classAverage) {
            return pow(($student->gpa - $classAverage), 2);
        }, $classExamResults);

        $stdDev = sqrt(array_sum($squaredDiffs) / count($classExamResults));

        $zScore = $stdDev > 0 ? ($currentExamResult->gpa - $classAverage) / $stdDev : 0;

        $passingCount = count(array_filter($classExamResults, function ($student) {
            return $student->exam_status === 'passed';
        }));

        $classPassRate = ($passingCount / count($classExamResults)) * 100;

        $individualPassStatus = $currentExamResult->exam_status === 'pass' ? 'Passed' : 'Failed';

        $performanceGap = max(array_column($classExamResults, 'gpa')) - min(array_column($classExamResults, 'gpa'));

        $scoreAsPercentOfAverage = $classAverage > 0 ? ($currentExamResult->gpa / $classAverage) * 100 : 0;

        return [
            'class_average' => $classAverage,
            'individual_score' => $currentExamResult->gpa,
            'performance_deviation' => $performanceDeviation,
            'percentage_above_average' => $percentageAboveAverage,
            'standard_deviation' => $stdDev,
            'z_score' => $zScore,
            'class_pass_rate' => $classPassRate,
            'individual_pass_status' => $individualPassStatus,
            'performance_gap' => $performanceGap,
            'score_as_percent_of_average' => $scoreAsPercentOfAverage,
        ];
    }
    public function analyzeGradeDistribution($marks) {
        $totalScore = 0;
        $totalGrades = 0;
        $passCount = 0;
        $failCount = 0;
        $courseScores = [];
        foreach ($marks as $mark) {
            $score = $mark->score;
            $gradeStatus = $mark->grade_status;

            $totalScore += $score;
            $totalGrades++;
            if ($gradeStatus === 'pass') {
                $passCount++;
            } else {
                $failCount++;
            }
            $courseId = $mark->course_id;
            if (!isset($courseScores[$courseId])) {
                $courseScores[$courseId] = ['totalScore' => 0, 'totalMarks' => 0];
            }
            $courseScores[$courseId]['totalScore'] += $score;
            $courseScores[$courseId]['totalMarks']++;
        }
        $overallAverage = ($totalGrades > 0) ? ($totalScore / $totalGrades) : 0;
        $passingRate = ($totalGrades > 0) ? ($passCount / $totalGrades) * 100 : 0;
        $failingRate = ($totalGrades > 0) ? ($failCount / $totalGrades) * 100 : 0;

        $courseAverages = [];
        foreach ($courseScores as $courseId => $scores) {
            $courseAverages[$courseId] = [
                'averageScore' => ($scores['totalMarks'] > 0) ? ($scores['totalScore'] / $scores['totalMarks']) : 0
            ];
        }

        return [
            'overall_average' => $overallAverage,
            'passing_rate' => $passingRate,
            'failing_rate' => $failingRate,
            'total_marks' => $totalGrades,
            'pass_count' => $passCount,
            'fail_count' => $failCount,
            'course_averages' => $courseAverages
        ];
    }
}
