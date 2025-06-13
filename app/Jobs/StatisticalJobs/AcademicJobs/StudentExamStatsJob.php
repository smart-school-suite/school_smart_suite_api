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
        $schoolBranchId = $this->student->school_branch_id;
        $schoolYear = $this->exam->school_year;
        $examTypeId = $this->exam->exam_type_id;
        $year = now()->year;
        $month = now()->month;
        $kpiNames =  [
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

        $decreaseByExamType = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults,
            'examType',
            'decrease'
        );
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

        // 2. Percentage Increase/Decrease Performance by Semester
        $increaseBySemester = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults,
            'semester',
            'increase'
        );
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

        $decreaseBySemester = $this->calculatePerformanceChange(
            $currentExamResult,
            $previousExamResults,
            'semester',
            'decrease'
        );
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

        // 3. Courses Sat, Passed, Failed
        $coursesSat = $this->coursesSat($marks);
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

        $coursesPassed = $this->coursesPassed($marks);
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

        $coursesFailed = $this->coursesFailed($marks);
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

        // 4. Pass Rate and Fail Rate
        $examPassRate = $this->examPassRate($marks);
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

        $examFailRate = $this->examFailRate($marks);
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

        // 5. Year-on-Year GPA and Total Score Changes
        $yearOnGpaChangesByExam = $this->yearOnGpaChangesByExam($studentResultsForGpaAndScoreChange);
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

        $yearOnTotalScoreChangesByExam = $this->yearOnTotalScoreByExam($studentResultsForGpaAndScoreChange);
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

        $gradesDistribution = $this->gradesDistribution($letterGrades, $marks);
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

        // 6. Potential Resits and Chances of Resit
        $totalPotentialResit = $this->totalNumberOfResits($marks);
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

        $totalNoResit = $this->totalNumberOfNoResits( $marks);
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

        $marksDistributionByCourse = $this->marksDistributionbyCourse($marks);
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

        // Perform a single bulk insert for all prepared data
        if (!empty($dataToBeInserted)) {
            DB::table('student_exam_stats')->insert($dataToBeInserted);
        }

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
    public function totalNumberOfResits(Collection $marks): int
    {
        return $marks->where('resit_status', 'resit')->count();
    }
   /**
     * Counts the total number of courses with a 'high_resit_potential' status for the student in the current exam.
     *
     * @param Collection $marks A collection of Marks models for the student in the current exam.
     * @return int The count of courses with 'high_resit_potential' status.
     */
    public function totalNumberOfNoResits(Collection $marks): int
    {
        return $marks->where('resit_status', 'no_resit')->count();
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

    public function marksDistributionbyCourse(Collection $marks): array {
        $courseMarksRanking = [];
         foreach($marks as $mark){
            $marks[] = [
                'course_code' => $mark->course->course_code,
                'course_title' => $mark->course->course_title,
                'score' => $mark->score
            ];
         }
        return $courseMarksRanking;
    }
}
