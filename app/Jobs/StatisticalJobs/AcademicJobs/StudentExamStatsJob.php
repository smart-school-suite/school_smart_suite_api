<?php

namespace App\Jobs\StatisticalJobs\AcademicJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Exams;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Models\StatTypes;
use App\Models\StudentResults;
use App\Models\Marks;
use App\Models\LetterGrade;
use Illuminate\Support\Facades\DB;

class StudentExamStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $exam;
    protected $student;

    /**
     * Create a new job instance.
     *
     * @param Exams $exam The exam model for which scores were submitted.
     * @param Student $student The student model.
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
        $schoolBranchId = $this->student->school_branch_id;
        $schoolYear = $this->exam->school_year;
        $examTypeId = $this->exam->exam_type_id;
        $year = now()->year;
        $month = now()->month;

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

        $currentExamResult = StudentResults::where('student_id', $this->student->id)
            ->where('exam_id', $this->exam->id)
            ->with(['exam.semester', 'exam.examtype'])
            ->first();

        if (!$currentExamResult) {
            return; // No current exam result found, so no stats to calculate for this student/exam
        }

        // Fetch previous results for the *same exam type* as the current exam
        // The whereHas is crucial to ensure the 'exam' and its 'examtype' relation exist
        $previousExamResults = StudentResults::where('student_id', $this->student->id)
            ->where('exam_id', '<>', $this->exam->id)
            ->whereHas('exam', function ($query) use ($examTypeId) {
                $query->where('exam_type_id', $examTypeId);
            })
            ->with(['exam.semester', 'exam.examtype'])
            ->get();

        // Fetch all marks for the current exam for the student
        $marks = Marks::where('student_id', $this->student->id)
            ->where('exam_id', $this->exam->id)
            ->with(['exams.semester', 'exams.examtype', 'course'])
            ->get();

        // Fetch all student results for the current student, filtered by current exam's type.
        // This is used for year-on-year changes, so it needs results from *all* exams of this type.
        $studentResultsForGpaAndScoreChange = StudentResults::where('student_id', $this->student->id)
            ->whereHas('exam', function ($query) use ($examTypeId) {
                $query->where('exam_type_id', $examTypeId);
            })
            ->with(['exam.semester', 'exam.examtype'])
            ->get();

        // Fetch StatTypes once and key them by 'name' for efficient lookup
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $letterGrades = LetterGrade::all();

        $dataToBeInserted = [];

        // --- KPI Calculations and Data Preparation ---

        // 1. Percentage Increase/Decrease Performance by Exam Type
        $increaseByExamType = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults,
            'examType',
            'increase'
        );
        if ($kpis->has('student_exam_percentage_increase_performance_by_exam_type')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_percentage_increase_performance_by_exam_type'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'decimal_value',
                $increaseByExamType
            );
        }


        $decreaseByExamType = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults,
            'examType',
            'decrease'
        );
        if ($kpis->has('student_exam_percentage_decrease_performance_by_exam_type')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_percentage_decrease_performance_by_exam_type'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'decimal_value',
                $decreaseByExamType
            );
        }

        // 2. Percentage Increase/Decrease Performance by Semester
        $increaseBySemester = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults, // Note: previousExamResults might need to be re-queried or filtered differently if semester comparison needs results *across* exam types for a semester. As per original code, it filters by examType from $previousExamResults.
            'semester',
            'increase'
        );
        if ($kpis->has('student_exam_percentage_increase_performance_by_semester')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_percentage_increase_performance_by_semester'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'decimal_value',
                $increaseBySemester
            );
        }

        $decreaseBySemester = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults, // Same note as above for previousExamResults filtering
            'semester',
            'decrease'
        );
        if ($kpis->has('student_exam_percentage_decrease_performance_by_semester')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_percentage_decrease_performance_by_semester'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'decimal_value',
                $decreaseBySemester
            );
        }

        // 3. Courses Sat, Passed, Failed
        $coursesSat = $this->coursesSat($marks);
        if ($kpis->has('student_exam_courses_sat')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_courses_sat'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'integer_value',
                $coursesSat
            );
        }

        $coursesPassed = $this->coursesPassed($marks);
        if ($kpis->has('student_exam_courses_passed')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_courses_passed'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'integer_value',
                $coursesPassed
            );
        }

        $coursesFailed = $this->coursesFailed($marks);
        if ($kpis->has('student_exam_courses_failed')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_courses_failed'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'integer_value',
                $coursesFailed
            );
        }

        // 4. Pass Rate and Fail Rate
        $examPassRate = $this->examPassRate($marks);
        if ($kpis->has('student_exam_pass_rate')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_pass_rate'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'decimal_value',
                $examPassRate
            );
        }

        $examFailRate = $this->examFailRate($marks);
        if ($kpis->has('student_exam_fail_rate')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_fail_rate'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'decimal_value',
                $examFailRate
            );
        }

        // 5. Year-on-Year GPA and Total Score Changes
        $yearOnGpaChangesByExam = $this->yearOnGpaChangesByExam($studentResultsForGpaAndScoreChange);
        if ($kpis->has('student_exam_school_year_on_gpa_changes_by_exam')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_school_year_on_gpa_changes_by_exam'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'json_value',
                json_encode($yearOnGpaChangesByExam)
            );
        }

        $yearOnTotalScoreChangesByExam = $this->yearOnTotalScoreByExam($studentResultsForGpaAndScoreChange);
        if ($kpis->has('student_exam_school_year_on_total_score_changes_by_exam')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_school_year_on_total_score_changes_by_exam'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'json_value',
                json_encode($yearOnTotalScoreChangesByExam)
            );
        }

        $gradesDistribution = $this->gradesDistribution($letterGrades, $marks);
        if ($kpis->has('student_exam_grades_distribution')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_grades_distribution'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'json_value',
                json_encode($gradesDistribution)
            );
        }

        // 6. Potential Resits and Chances of Resit
        $totalPotentialResit = $this->totalNumberOfResits($marks);
        if ($kpis->has('student_exam_resits')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_resits'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'integer_value',
                $totalPotentialResit
            );
        }

        $totalNoResit = $this->totalNumberOfNoResits($marks);
        if ($kpis->has('student_exam_no_resit')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_no_resit'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'integer_value',
                $totalNoResit
            );
        }

        $marksDistributionByCourse = $this->marksDistributionbyCourse($marks);
        if ($kpis->has('student_exam_marks_score_distribution_by_course')) {
            $dataToBeInserted[] = $this->prepareStatData(
                $kpis->get('student_exam_marks_score_distribution_by_course'),
                $this->exam->id,
                $schoolBranchId,
                $this->student->id,
                $schoolYear,
                $month,
                $year,
                'json_value',
                json_encode($marksDistributionByCourse)
            );
        }

        // Perform a single bulk insert for all prepared data
        if (!empty($dataToBeInserted)) {
            DB::table('student_exam_stats')->insert($dataToBeInserted);
        }
    }

    /**
     * Prepares an array of data for insertion into the student_exam_stats table.
     *
     * @param StatTypes|null $kpi The StatTypes model instance, or null if not found.
     * @param string $examId The ID of the exam.
     * @param string $schoolBranchId The ID of the school branch.
     * @param string $studentId The ID of the student.
     * @param string $schoolYear The school year.
     * @param int $month The current month.
     * @param int $year The current year.
     * @param string $valueType The type of value ('decimal_value', 'integer_value', 'json_value').
     * @param mixed $value The actual value to be stored.
     * @return array The prepared data array.
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
            'decimal_value' => null,
            'integer_value' => null,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null, // Use null coalescing to handle cases where $kpi might be null
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
     * @param StudentResults $currentExamResult The current exam result for the student.
     * @param Collection $previousExamResults A collection of previous exam results for the student.
     * @param string $comparisonBasis Specifies how to filter previous results: 'examType' or 'semester'.
     * @param string $changeType Specifies the type of change to return: 'increase' or 'decrease'.
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

        // Determine the comparison ID for the current exam result
        $comparisonId = null;
        if ($comparisonBasis === 'examType') {
            $comparisonId = $currentExamResult->exam->examtype->id ?? null;
        } elseif ($comparisonBasis === 'semester') {
            $comparisonId = $currentExamResult->exam->semester->id ?? null;
        }

        // If the current exam's comparison ID cannot be determined, we can't compare
        if ($comparisonId === null) {
            return ($changeType === 'increase') ? 0.00 : 0.00; // No basis for comparison, no change
        }

        // Filter previous results, ensuring relationships exist before accessing their properties
        $filteredPreviousResults = $previousExamResults->filter(function ($previousResult) use ($comparisonBasis, $comparisonId) {
            $prevComparisonId = null;

            // Safely access nested relationships using optional chaining or null coalescing
            if ($comparisonBasis === 'examType') {
                $prevComparisonId = $previousResult->exam->examtype->id ?? null;
            } elseif ($comparisonBasis === 'semester') {
                $prevComparisonId = $previousResult->exam->semester->id ?? null;
            }

            return $prevComparisonId === $comparisonId;
        });

        if ($filteredPreviousResults->isEmpty()) {
            // If no relevant previous results are found for comparison
            // If it's an increase type, and no previous results, it's a 100% "increase" from nothing.
            // If it's a decrease type, and no previous results, there's no decrease, so 0.
            return ($changeType === 'increase') ? 100.00 : 0.00;
        }

        $previousAverageGpa = $filteredPreviousResults->avg('gpa');

        // Handle division by zero for previousAverageGpa
        if ($previousAverageGpa == 0) {
            // If previous average was 0:
            // - If current score is positive, it's a 100% increase.
            // - Otherwise (current score is 0 or negative), no change / no meaningful increase/decrease.
            return ($currentScore > 0) ? 100.00 : 0.00;
        }

        $percentageChange = (($currentScore - $previousAverageGpa) / $previousAverageGpa) * 100;

        if ($changeType === 'increase') {
            // Only return positive changes for 'increase'
            return max(0.00, round($percentageChange, 2));
        } else {
            // Only return negative changes for 'decrease'
            return min(0.00, round($percentageChange, 2));
        }
    }

    /**
     * Counts the number of courses a student sat for in the given exam.
     *
     * @param Collection<int, Marks> $marks A collection of Marks models for the student in the current exam.
     * @return int The total number of courses sat.
     */
    public function coursesSat(Collection $marks): int
    {
        return $marks->count();
    }

    /**
     * Counts the number of courses a student passed in the given exam.
     *
     * @param Collection<int, Marks> $marks A collection of Marks models for the student in the current exam.
     * @return int The total number of courses passed.
     */
    public function coursesPassed(Collection $marks): int
    {
        return $marks->where('grade_status', 'passed')->count();
    }

    /**
     * Counts the number of courses a student failed in the given exam.
     *
     * @param Collection<int, Marks> $marks A collection of Marks models for the student in the current exam.
     * @return int The total number of courses failed.
     */
    public function coursesFailed(Collection $marks): int
    {
        return $marks->where('grade_status', 'failed')->count();
    }

    /**
     * Calculates the pass rate for the given exam.
     *
     * @param Collection<int, Marks> $marks A collection of Marks models for the student in the current exam.
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
     * @param Collection<int, Marks> $marks A collection of Marks models for the student in the current exam.
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
     * @param Collection<int, StudentResults> $results A collection of StudentResults models for the student,
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
     * @param Collection<int, StudentResults> $results A collection of StudentResults models for the student,
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
     * Counts the total number of courses requiring a resit for the student in the current exam.
     *
     * @param Collection<int, Marks> $marks A collection of Marks models for the student in the current exam.
     * @return int The count of courses with 'resit' status.
     */
    public function totalNumberOfResits(Collection $marks): int
    {
        return $marks->where('resit_status', 'resit')->count();
    }

    /**
     * Counts the total number of courses not requiring a resit for the student in the current exam.
     *
     * @param Collection<int, Marks> $marks A collection of Marks models for the student in the current exam.
     * @return int The count of courses with 'no_resit' status.
     */
    public function totalNumberOfNoResits(Collection $marks): int
    {
        return $marks->where('resit_status', 'no_resit')->count();
    }

    /**
     * Calculates the distribution of grades for the student in the current exam.
     *
     * @param Collection<int, LetterGrade> $letterGrades A collection of all defined LetterGrade models.
     * @param Collection<int, Marks> $marks A collection of Marks models for the student in the current exam.
     * @return array An array of grade distribution, with each element containing 'letter_grade' and 'count'.
     */
    public function gradesDistribution(Collection $letterGrades, Collection $marks): array
    {
        $distribution = [];
        // Initialize distribution with all possible letter grades and a count of 0
        foreach ($letterGrades as $gradeDefinition) {
            $letterGrade = $gradeDefinition->letter_grade;
            $distribution[$letterGrade] = [
                'letter_grade' => $letterGrade,
                'count' => 0,
            ];
        }

        // Increment count for each assigned grade
        foreach ($marks as $mark) {
            $assignedGrade = $mark->grade ?? null;

            if ($assignedGrade && isset($distribution[$assignedGrade])) {
                $distribution[$assignedGrade]['count']++;
            }
        }
        return array_values($distribution); // Return as a numerically indexed array
    }

    /**
     * Organizes marks distribution by course for the student in the current exam.
     *
     * @param Collection<int, Marks> $marks A collection of Marks models for the student in the current exam.
     * @return array An array of associative arrays, each containing 'course_code', 'course_title', and 'score'.
     */
    public function marksDistributionbyCourse(Collection $marks): array
    {
        $courseMarksRanking = [];
        foreach ($marks as $mark) {
            // Ensure course relationship exists before accessing properties
            if ($mark->course) {
                $courseMarksRanking[] = [ // Append to $courseMarksRanking, not overwrite $marks
                    'course_code' => $mark->course->course_code,
                    'course_title' => $mark->course->course_title,
                    'score' => $mark->score
                ];
            }
        }
        return $courseMarksRanking;
    }
}
