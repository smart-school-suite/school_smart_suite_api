<?php

namespace App\Jobs\StatisticalJobs\AcademicJobs;

use App\Models\LetterGrade;
use App\Models\StudentResults;
use App\Models\Marks;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class StudentCaStatsJob
 *
 * This job calculates and stores various academic statistics for a given student
 * and exam, focusing on Continuous Assessment (CA) related metrics.
 */
class StudentCaStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected  $exam;
    protected $student;



    public function __construct($exam,  $student)
    {
        $this->exam = $exam;
        $this->student = $student;
    }

    /**
     * Execute the job.
     *
     * This method orchestrates the calculation of various student academic
     * statistics and inserts them into the database.
     */
    public function handle(): void
    {
        $schoolBranchId = $this->student->school_branch_id;
        $schoolYear = $this->exam->school_year;
        $examTypeId = $this->exam->exam_type_id;
        $year = now()->year;
        $month = now()->month;

        //KPI names for consistent lookup and clarity
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



        $currentExamResult = StudentResults::where('student_id', $this->student->id)
            ->where('exam_id', $this->exam->id)
            ->with(['exam.semester', 'exam.examType'])
            ->first();


        if (!$currentExamResult) {
            return;
        }


        // Fetch previous results for the *same exam type* as the current exam
        $previousExamResults = StudentResults::where('student_id', $this->student->id)
            ->where('exam_id', '<>', $this->exam->id)
            ->with([
                'exam' => function ($query) use ($examTypeId) {
                    $query->where('exam_type_id', $examTypeId);
                },
                'exam.semester',
                'exam.examType'
            ])
            ->get();


        // Fetch all marks for the current exam for the student
        $marks = Marks::where('student_id', $this->student->id)
            ->where('exam_id', $this->exam->id)
            ->with(['exams.semester', 'exams.examType', 'course'])
            ->get();

        // Fetch all student results for the current exam type, across all exams,
        $studentResultsForGpaAndScoreChange = StudentResults::where('student_id', $this->student->id)
            ->with([
                'exam' => function ($query) use ($examTypeId) {
                    $query->where('exam_type_id', $examTypeId);
                },
                'exam.semester',
                'exam.examType'
            ]) // Only need schoolYear for this specific KPI
            ->get();

        // Fetch StatTypes once and key them by 'name' for efficient lookup
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $letterGrades = LetterGrade::all();

        $dataToBeInserted = [];


        // 1. Percentage Increase/Decrease Performance by Exam Type
        $increaseByExamType = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults,
            'examType',
            'increase'
        );
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('percentage_increase_performance_by_exam_type'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'decimal_value',
            $increaseByExamType
        );

        $decreaseByExamType = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults,
            'examType',
            'decrease'
        );
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('percentage_decrease_performance_by_exam'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'decimal_value',
            $decreaseByExamType
        );

        // 2. Percentage Increase/Decrease Performance by Semester
        $increaseBySemester = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults,
            'semester',
            'increase'
        );
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('percentage_increase_performance_by_semester'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'decimal_value',
            $increaseBySemester
        );

        $decreaseBySemester = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults,
            'semester',
            'decrease'
        );
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('percentage_decrease_performance_by_semester'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'decimal_value',
            $decreaseBySemester
        );

        // 3. Courses Sat, Passed, Failed
        $coursesSat = $this->coursesSat($marks);
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('courses_sat'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'integer_value',
            $coursesSat
        );

        $coursesPassed = $this->coursesPassed($marks);
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('courses_passed'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'integer_value',
            $coursesPassed
        );

        $coursesFailed = $this->coursesFailed($marks);
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('courses_failed'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'integer_value',
            $coursesFailed
        );

        // 4. Pass Rate and Fail Rate
        $examPassRate = $this->examPassRate($marks);
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('pass_rate'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'decimal_value',
            $examPassRate
        );

        $examFailRate = $this->examFailRate($marks);
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('fail_rate'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'decimal_value',
            $examFailRate
        );

        // 5. Year-on-Year GPA and Total Score Changes
        $yearOnGpaChangesByExam = $this->yearOnGpaChangesByExam($studentResultsForGpaAndScoreChange);
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('school_year_on_gpa_changes_by_exam'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'json_value',
            json_encode($yearOnGpaChangesByExam)
        );

        $yearOnTotalScoreChangesByExam = $this->yearOnTotalScoreByExam($studentResultsForGpaAndScoreChange);
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('school_year_on_total_score_changes_by_exam'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'json_value',
            json_encode($yearOnTotalScoreChangesByExam)
        );

        $gradesDistribution = $this->gradesDistribution($letterGrades, $marks);
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('grades_distribution'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'json_value',
            json_encode($gradesDistribution)
        );

        // 6. Potential Resits and Chances of Resit
        $totalPotentialResit = $this->totalNumberOfPotResits($marks);
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('potential_resits'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'integer_value',
            $totalPotentialResit
        );

        $resitChances = $this->determineResitChance($this->exam->weighted_mark, $marks);
        $dataToBeInserted[] = $this->prepareStatData(
            $kpis->get('chances_of_resit'),
            $this->exam->id,
            $schoolBranchId,
            $this->student->id,
            $schoolYear,
            $month,
            $year,
            'json_value',
            json_encode($resitChances)
        );

        // Perform a single bulk insert for all prepared data
        if (!empty($dataToBeInserted)) {
            DB::table('student_exam_stats')->insert($dataToBeInserted);
        }
    }

    /**
     * Prepares an array for inserting a single statistic record into the database.
     *
     * @param StatTypes|null $kpi           The StatType model instance for the KPI.
     * Can be null if the KPI name was not found in the StatTypes table.
     * @param string         $examId        The ID of the exam.
     * @param string         $schoolBranchId The ID of the school branch.
     * @param string         $studentId     The ID of the student.
     * @param string         $schoolYear    The school year.
     * @param int            $month         The current month.
     * @param int            $year          The current year.
     * @param string         $valueType     The type of value column ('decimal_value', 'integer_value', 'json_value').
     * @param mixed          $value         The actual value to be stored for the statistic.
     * @return array The associative array ready for database insertion.
     */
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

    /**
     * Calculates the percentage change in performance (increase or decrease) based on GPA.
     * This method is generic for both exam type and semester comparisons.
     *
     * @param StudentResults $currentExamResult   The current exam result for the student.
     * @param Collection     $previousExamResults A collection of previous exam results for the student.
     * @param string         $comparisonBasis     Specifies how to filter previous results: 'examType' or 'semester'.
     * @param string         $changeType          Specifies the type of change to return: 'increase' or 'decrease'.
     * @return float The calculated percentage change. Returns 0.0 if current result is null or no relevant previous results,
     * or 100.0 if previous average is 0 and current is positive for 'increase', or 0.0 for 'decrease'.
     */
    private function calculatePerformanceChange(
        StudentResults $currentExamResult,
        Collection $previousExamResults,
        string $comparisonBasis,
        string $changeType
    ): float {
        $currentScore = $currentExamResult->gpa;

        $comparisonId = null;
        if ($comparisonBasis === 'examType' && $currentExamResult->exam->examType) {
            $comparisonId = $currentExamResult->exam->examType->id;
        } elseif ($comparisonBasis === 'semester' && $currentExamResult->exam->semester) {
            $comparisonId = $currentExamResult->exam->semester->id;
        }

        $filteredPreviousResults = $previousExamResults->filter(function ($previousResult) use ($comparisonBasis, $comparisonId) {
            $prevComparisonId = null;
            if ($comparisonBasis === 'examType' && $previousResult->exam->examType) {
                $prevComparisonId = $previousResult->exam->examType->id;
            } elseif ($comparisonBasis === 'semester' && $previousResult->exam->semester) {
                $prevComparisonId = $previousResult->exam->semester->id;
            }
            return $prevComparisonId === $comparisonId;
        });

        if ($filteredPreviousResults->isEmpty()) {
            return ($changeType === 'increase') ? 100.00 : 0.00;
        }

        $previousAverageGpa = $filteredPreviousResults->avg('gpa');

        if ($previousAverageGpa == 0) {
            return ($currentScore > 0) ? 100.00 : 0.00;
        }

        $percentageChange = (($currentScore - $previousAverageGpa) / $previousAverageGpa) * 100;

        if ($changeType === 'increase') {
            return max(0.00, round($percentageChange, 2));
        } else {
            return min(0.00, round($percentageChange, 2));
        }
    }

    /**
     * Counts the number of courses a student sat for in the given exam.
     *
     * @param Collection $marks A collection of Marks models for the student in the current exam.
     * @return int The total number of courses sat.
     */
    public function coursesSat(Collection $marks): int
    {
        return $marks->count();
    }

    /**
     * Counts the number of courses a student passed in the given exam.
     *
     * @param Collection $marks A collection of Marks models for the student in the current exam.
     * @return int The total number of courses passed.
     */
    public function coursesPassed(Collection $marks): int
    {
        return $marks->where('grade_status', 'passed')->count();
    }

    /**
     * Counts the number of courses a student failed in the given exam.
     *
     * @param Collection $marks A collection of Marks models for the student in the current exam.
     * @return int The total number of courses failed.
     */
    public function coursesFailed(Collection $marks): int
    {
        return $marks->where('grade_status', 'failed')->count();
    }

    /**
     * Calculates the pass rate for the given exam.
     *
     * @param Collection $marks A collection of Marks models for the student in the current exam.
     * @return float The pass rate as a percentage, rounded to two decimal places. Returns 0.0 if no courses were sat.
     */
    public function examPassRate(Collection $marks): float
    {
        $coursesSat = $marks->count();
        if ($coursesSat === 0) {
            return 0.0;
        }
        $coursesPassedCount = $this->coursesPassed($marks);
        return round(($coursesPassedCount / $coursesSat) * 100, 2);
    }

    /**
     * Calculates the fail rate for the given exam.
     *
     * @param Collection $marks A collection of Marks models for the student in the current exam.
     * @return float The fail rate as a percentage, rounded to two decimal places. Returns 0.0 if no courses were sat.
     */
    public function examFailRate(Collection $marks): float
    {
        $coursesSat = $marks->count();
        if ($coursesSat === 0) {
            return 0.0;
        }
        $coursesFailedCount = $this->coursesFailed($marks);
        return round(($coursesFailedCount / $coursesSat) * 100, 2);
    }

    /**
     * Prepares data for year-on-year GPA changes for a student across exams of a specific type.
     *
     * @param Collection $results A collection of StudentResults models for the student,
     * filtered by exam type and eager loaded with exam school year.
     * @return array An array of associative arrays, each containing 'school_year' and 'gpa'.
     */
    public function yearOnGpaChangesByExam(Collection $results): array
    {
        return $results->map(function ($result) {
            return [
                'school_year' => $result->exam->school_year ?? null,
                'gpa' => $result->gpa,
            ];
        })->toArray();
    }

    /**
     * Prepares data for year-on-year total score changes for a student across exams of a specific type.
     *
     * @param Collection $results A collection of StudentResults models for the student,
     * filtered by exam type and eager loaded with exam school year.
     * @return array An array of associative arrays, each containing 'school_year' and 'total_score'.
     */
    public function yearOnTotalScoreByExam(Collection $results): array
    {
        return $results->map(function ($result) {
            return [
                'school_year' => $result->exam->school_year ?? null,
                'total_score' => $result->total_score,
            ];
        })->toArray();
    }

    /**
     * Counts the total number of courses with a 'high_resit_potential' status for the student in the current exam.
     *
     * @param Collection $marks A collection of Marks models for the student in the current exam.
     * @return int The count of courses with 'high_resit_potential' status.
     */
    public function totalNumberOfPotResits(Collection $marks): int
    {
        return $marks->where('resit_status', 'high_resit_potential')->count();
    }

    /**
     * Determines the chance of failure for each course based on the student's score
     * against a maximum possible score for the exam.
     *
     * @param float      $maxScore      The maximum possible score for an individual course within the exam.
     * @param Collection $studentScores A collection of Marks models for the student in the current exam.
     * @return array An array of associative arrays, each containing course details (title, code)
     * and the calculated chance of failure (rounded to two decimal places).
     */
    public static function determineResitChance(float $maxScore, Collection $studentScores): array
    {
        if ($maxScore <= 0) {
            return []; // Cannot calculate chance of failure if max score is zero or negative
        }

        $results = [];
        foreach ($studentScores as $studentCourse) {
            // Access course details robustly, checking for both array and object access
            $courseTitle = $studentCourse['courses']['course_title'] ?? ($studentCourse->course->course_title ?? 'N/A');
            $courseCode = $studentCourse['courses']['course_code'] ?? ($studentCourse->course->course_code ?? 'N/A');
            $score = $studentCourse['score'] ?? 0;

            // Ensure the effective score doesn't exceed the maxScore (cap it)
            $effectiveScore = min($score, $maxScore);
            $scorePercentage = ($effectiveScore / $maxScore) * 100;

            // Chance of failure is 100% minus the score percentage, capped at 0%
            $chanceOfFailure = max(0.0, 100.0 - $scorePercentage);

            $results[] = [
                'course_title'      => $courseTitle,
                'course_code'       => $courseCode,
                'chance_of_failure' => round($chanceOfFailure, 2),
            ];
        }

        return $results;
    }

    public function gradesDistribution(Collection $letterGrades, Collection $marks): array
    {
        $distribution = [];
        foreach ($letterGrades as $gradeDefinition) {
            $letterGrade = $gradeDefinition->letter_grade;
            $distribution[$letterGrade] = [
                'letter_grade' => $letterGrade,
                'count' => 0,
            ];
        }
        foreach ($marks as $mark) {
            $assignedGrade = $mark->grade ?? null;

            if ($assignedGrade && isset($distribution[$assignedGrade])) {
                $distribution[$assignedGrade]['count']++;
            }
        }
        return array_values($distribution);
    }
}
