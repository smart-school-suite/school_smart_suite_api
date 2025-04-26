<?php

namespace App\Jobs\StatisticalJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Marks;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\StudentResults;
use Illuminate\Queue\SerializesModels;

class StudentExamStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $examId;
    protected $studentId; // Add student ID

    /**
     * Create a new job instance.
     *
     * @param int $examId The ID of the exam for which scores were submitted.
     * @param int $studentId The ID of the student.
     */
    public function __construct(int $examId, int $studentId)
    {
        $this->examId = $examId;
        $this->studentId = $studentId; // Store the student ID
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Starting KPI calculation for exam ID: {$this->examId}, Student ID: {$this->studentId}");

        try {
            $this->calculateAndStoreKPIs();
            Log::info("KPI calculation complete for exam ID: {$this->examId}, Student ID: {$this->studentId}");
        } catch (\Exception $e) {
            Log::error("Error calculating KPIs for exam ID: {$this->examId}, Student ID: {$this->studentId}. Error: {$e->getMessage()}");
            // Consider re-throwing or handling the exception as needed.
        }
    }

    private function calculateAndStoreKPIs(): void
    {
        $kpis = $this->getKpis();

        foreach ($kpis as $kpi) {
            $this->calculateAndStoreKpi($kpi['name'], $kpi['program_name']);
        }
    }

    private function getKpis(): array
    {
        return [
            [
                "name" => "Courses Sat",
                "program_name" => "courses_sat",
            ],
            [
                "name" => "Courses Passed",
                "program_name" => "courses_passed",
            ],
            [
                "name" => "Courses Failed",
                "program_name" => "courses_failed",
            ],
            [
                "name" => "Pass Rate by Course",
                "program_name" => "pass_rate_by_course",
            ],
            [
                "name" => "Fail Rate by Course",
                "program_name" => "fail_rate_by_course",
            ],
            [
                "name" => "Total Scores by Course",
                "program_name" => "total_scores_by_course",
            ],
            [
                "name" => "Grades Distribution by Course",
                "program_name" => "grades_distribution_by_course",
            ],
            [
                "name" => "Overall Score",
                "program_name" => "overall_score",
            ],
            [
                "name" => "Subject/Topic Proficiency",
                "program_name" => "subject_topic_proficiency",
            ],
            [
                "name" => "Progress Over Time",
                "program_name" => "progress_over_time",
            ],
            [
                "name" => "Class Average vs. Individual Performance",
                "program_name" => "class_avg_vs_individual_performance",
            ],
            [
                "name" => "Student Ranking",
                "program_name" => "student_ranking",
            ],
            [
                "name" => "Percentage Increase in Performance",
                "program_name" => "percentage_increase_performance",
            ],
            [
                "name" => "Percentage Decrease in Performance",
                "program_name" => "percentage_decrease_performance",
            ],
            [
                "name" => "Score Trends Over Time",
                "program_name" => "score_trends_over_time",
            ],
            [
                "name" => "Consistency of Scores",
                "program_name" => "consistency_of_scores",
            ],
            [
                "name" => "Average Class Performance", //This was not student specific
                "program_name" => "average_class_performance",
            ],
            [
                "name" => "Best Exam Score",  //This was not student specific
                "program_name" => "best_exam_score",
            ],
            [
                "name" => "Worst Exam Score", //This was not student specific
                "program_name" => "worst_exam_score",
            ],
        ];
    }

    private function calculateAndStoreKpi(string $kpiName, string $programName): void
    {
        Log::info("Calculating: {$kpiName} for exam ID: {$this->examId}, Student ID: {$this->studentId}");
        $result = null;

        switch ($programName) {
            case 'courses_sat':
                $result = $this->calculateCoursesSat();
                break;
            case 'courses_passed':
                $result = $this->calculateCoursesPassed();
                break;
            case 'courses_failed':
                $result = $this->calculateCoursesFailed();
                break;
            case 'pass_rate_by_course':
                $result = $this->calculatePassRateByCourse();
                break;
            case 'fail_rate_by_course':
                $result = $this->calculateFailRateByCourse();
                break;
            case 'total_scores_by_course':
                $result = $this->calculateTotalScoresByCourse();
                break;
            case 'grades_distribution_by_course':
                $result = $this->calculateGradesDistributionByCourse();
                break;
            case 'overall_score':
                $result = $this->calculateOverallScore();
                break;
            case 'subject_topic_proficiency':
                $result = $this->calculateSubjectTopicProficiency();
                break;
            case 'progress_over_time':
                $result = $this->calculateProgressOverTime();
                break;
            case 'class_avg_vs_individual_performance':
                $result = $this->calculateClassAverageVsIndividualPerformance();
                break;
            case 'student_ranking':
                $result = $this->calculateStudentRanking();
                break;
            case 'percentage_increase_performance':
            case 'percentage_decrease_performance':
                $result = $this->calculatePercentagePerformance($programName);
                break;
            case 'score_trends_over_time':
                $result = $this->calculateScoreTrendsOverTime();
                break;
            case 'consistency_of_scores':
                $result = $this->calculateConsistencyOfScores();
                break;
            case 'average_class_performance': //These are not student specific
                $result = $this->calculateAverageClassPerformance();
                break;
            case 'best_exam_score':  //These are not student specific
                $result = $this->calculateBestExamScore();
                break;
            case 'worst_exam_score': //These are not student specific
                $result = $this->calculateWorstExamScore();
                break;
            default:
                Log::warning("No calculation logic defined for '{$programName}'.");
                return;
        }

        $this->storeKpiResult($kpiName, $programName, $result);
    }

    private function storeKpiResult(string $kpiName, string $programName, $result): void
    {
        if ($result !== null) {

            Log::info("{$kpiName} calculated and stored for exam ID: {$this->examId}, Student ID: {$this->studentId}");
        }
    }

    private function calculateCoursesSat(): int
    {
        return Marks::where('exam_id', $this->examId)
            ->where('student_id', $this->studentId) // Filter by student
            ->distinct('course_id')
            ->count();
    }

    private function calculateCoursesPassed(): int
    {
        return Marks::where('exam_id', $this->examId)
            ->where('student_id', $this->studentId)
            ->where('grade_status', 'PASS')
            ->distinct('course_id')
            ->count();
    }

    private function calculateCoursesFailed(): int
    {
        return Marks::where('exam_id', $this->examId)
            ->where('student_id', $this->studentId)
            ->where('grade_status', 'FAILED')
            ->distinct('course_id')
            ->count();
    }

    private function calculatePassRateByCourse(): array
    {
        return Marks::where('exam_id', $this->examId)
            ->where('student_id', $this->studentId)
            ->select('course_id', DB::raw('SUM(CASE WHEN grade_status = "PASS" THEN 1 ELSE 0 END) as passed_count'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('course_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->course_id => $item->total_count > 0 ? ($item->passed_count / $item->total_count) * 100 : 0];
            })
            ->toArray();
    }

    private function calculateFailRateByCourse(): array
    {
        return Marks::where('exam_id', $this->examId)
            ->where('student_id', $this->studentId)
            ->select('course_id', DB::raw('SUM(CASE WHEN grade_status = "FAILED" THEN 1 ELSE 0 END) as failed_count'), DB::raw('COUNT(*) as total_count'))
            ->groupBy('course_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->course_id => $item->total_count > 0 ? ($item->failed_count / $item->total_count) * 100 : 0];
            })
            ->toArray();
    }

    private function calculateTotalScoresByCourse(): array
    {
        return Marks::where('exam_id', $this->examId)
            ->where('student_id', $this->studentId)
            ->select('course_id', DB::raw('SUM(score) as total_score'))
            ->groupBy('course_id')
            ->get()
            ->pluck('total_score', 'course_id')
            ->toArray();
    }

    private function calculateGradesDistributionByCourse(): array
    {
        return Marks::where('exam_id', $this->examId)
            ->where('student_id', $this->studentId)
            ->select('course_id', 'grade', DB::raw('COUNT(*) as count'))
            ->groupBy('course_id', 'grade')
            ->get()
            ->groupBy('course_id')
            ->map(function ($courseGrades) {
                return $courseGrades->pluck('count', 'grade')->toArray();
            })
            ->toArray();
    }

    private function calculateOverallScore(): array
    {
        return StudentResults::where('exam_id', $this->examId)
            ->where('student_id', $this->studentId) // Filter by student
            ->pluck('total_score')
            ->toArray(); // Returns a simple array of overall scores
    }

    private function calculateSubjectTopicProficiency()
    {
        //  Needs Implementation.  Requires student_id
        return 'Calculation logic for Subject/Topic Proficiency needs more details.';
    }

    private function calculateProgressOverTime()
    {
        // Needs Implementation. Requires student_id
        return 'Calculation logic for Progress Over Time needs more details about time tracking.';
    }

    private function calculateClassAverageVsIndividualPerformance(): array
    {
        $classAverages = Marks::where('exam_id', $this->examId)
            ->select('course_id', DB::raw('AVG(score) as average_score'))
            ->groupBy('course_id')
            ->get()
            ->pluck('average_score', 'course_id')
            ->toArray();

        $individualScores = Marks::where('exam_id', $this->examId)
            ->where('student_id', $this->studentId)
            ->select('student_id', 'course_id', 'score')
            ->get()
            ->groupBy('student_id')
            ->map(function ($studentMarks) use ($classAverages) {
                return $studentMarks->mapWithKeys(function ($mark) use ($classAverages) {
                    return [$mark->course_id => ['individual_score' => $mark->score, 'class_average' => $classAverages[$mark->course_id] ?? null]];
                })->toArray();
            })
            ->toArray();
        return $individualScores;
    }

    private function calculateStudentRanking(): array
    {
        $studentRankings = StudentResults::where('exam_id', $this->examId)
            ->orderByDesc('total_score')
            ->pluck('student_id')
            ->toArray();

        $studentRankRanks = array_flip($studentRankings);
        // Ensure the ranking is for the correct student.
        return [$this->studentId => $studentRankRanks[$this->studentId] ?? null];
    }

    private function calculatePercentagePerformance(string $type)
    {
        // Needs Implementation. Requires student_id
        return "Calculation logic for {$type} needs definition of performance metric and time periods.";
    }

    private function calculateScoreTrendsOverTime()
    {
        // Needs Implementation. Requires student_id
        return 'Calculation logic for Score Trends Over Time needs time-based data.';
    }

    private function calculateConsistencyOfScores(): array
    {
        $studentConsistency = Marks::where('exam_id', $this->examId)
            ->where('student_id', $this->studentId)
            ->select('student_id', 'score')
            ->get()
            ->groupBy('student_id')
            ->map(function ($studentMarks) {
                if ($studentMarks->count() > 1) {
                    return collect($studentMarks->pluck('score'))->stdDev();
                }
                return null;
            })
            ->filter()
            ->toArray();
        return $studentConsistency;
    }

    private function calculateAverageClassPerformance(): float
    {
        return  Marks::where('exam_id', $this->examId)->avg('score');
    }

    private function calculateBestExamScore(): float
    {
        return  Marks::where('exam_id', $this->examId)->max('score');
    }

    private function calculateWorstExamScore(): float
    {
        return  Marks::where('exam_id', $this->examId)->min('score');
    }
}
