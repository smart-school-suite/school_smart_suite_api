<?php

namespace App\Jobs\StatisticalJobs\AcademicJobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\StudentResults;
use App\Models\Marks;
use App\Models\StatTypes;
use App\Models\LetterGrade;
use Illuminate\Support\Facades\Log;
use Throwable; // Import Throwable for catch block

class CaStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $exam;


    public function __construct($exam)
    {
        $this->exam = $exam;
    }

  /**
     * Execute the job.
     * This method calculates various academic statistics for a given exam
     * and stores them in the database.
     */
    public function handle(): void
    {
        // Extract relevant exam properties for cleaner access
        $schoolBranchId = $this->exam->school_branch_id;
        $schoolYear = $this->exam->school_year;
        $examTypeId = $this->exam->exam_type_id;
        $year = now()->year;
        $month = now()->month;

        // Define the KPI names we are interested in.
        // These should correspond to 'program_name' in the stat_types table.
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

        // Retrieve necessary data efficiently
        // Using `toBase()` to work with generic objects/arrays for simpler calculations,
        // reducing Eloquent model overhead in intense loops if relationships aren't needed there.
        $examResults = StudentResults::where("exam_id", $this->exam->id)->get()->toBase();
        // Eager load 'course' relationship for Marks to avoid N+1 queries in loops
        $studentMarks = Marks::where("exam_id", $this->exam->id)->with('course')->get()->toBase();
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');
        $letterGrades = LetterGrade::all()->toBase();

        $dataToInsert = [];

        try {
            DB::beginTransaction();

            // --- 1. Overall Exam Statistics ---

            $totalStudents = $examResults->count();
            $passedStudentsCount = $examResults->where('exam_status', 'passed')->count();
            $failedStudentsCount = $examResults->where('exam_status', 'failed')->count();

            // Total Students Accessed
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_total_students_accessed'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'integer_value', $totalStudents
            );

            // Total Students Passed
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_total_students_passed'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'integer_value', $passedStudentsCount
            );

            // Total Students Failed
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_total_students_failed'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'integer_value', $failedStudentsCount
            );

            // Exam Pass Rate
            $examPassRate = $totalStudents > 0 ? round(($passedStudentsCount / $totalStudents) * 100, 2) : 0.00;
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_pass_rate'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'decimal_value', $examPassRate
            );

            // Exam Fail Rate
            $examFailRate = $totalStudents > 0 ? round(($failedStudentsCount / $totalStudents) * 100, 2) : 0.00;
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_fail_rate'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'decimal_value', $examFailRate
            );

            // Average Total Score
            $totalScoreSum = $examResults->sum('total_score');
            $examAverageTotalScore = $totalStudents > 0 ? round($totalScoreSum / $totalStudents, 2) : 0.00;
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('average_ca_exam_total_score'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'decimal_value', $examAverageTotalScore
            );

            // Average GPA
            $totalGpaSum = $examResults->sum('gpa');
            $examAverageGpa = $totalStudents > 0 ? round($totalGpaSum / $totalStudents, 2) : 0.00;
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('average_ca_exam_gpa'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'decimal_value', $examAverageGpa
            );

            // Grades Distribution by Exam
            $examGradesDistribute = $this->gradesDistributionByExam($studentMarks, $letterGrades);
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_grades_distribution'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'json_value', json_encode($examGradesDistribute)
            );

            // --- 2. Course-level Statistics ---
            $courseStats = $this->analyzeCourseStatistics($studentMarks);

            // Course Pass Rates
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_course_pass_rates'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'json_value', json_encode($courseStats['pass_rates'])
            );

            // Course Fail Rates
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_course_fail_rates'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'json_value', json_encode($courseStats['fail_rates'])
            );

            // Course Pass Distribution (more granular, if needed, otherwise 'pass_rates' already covers this)
            // Assuming 'distribution' implies a detailed list of courses with their pass/fail counts, not just rates.
            $coursePassDistribution = $studentMarks->groupBy('course.course_title')->map(function ($marksPerCourse, $courseTitle) {
                return [
                    'course_title' => $courseTitle,
                    'total_students' => $marksPerCourse->count(),
                    'passed_students' => $marksPerCourse->where('grade_status', 'passed')->count(),
                ];
            })->values()->toArray();
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_course_pass_distribution'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'json_value', json_encode($coursePassDistribution)
            );

            // Course Fail Distribution
            $courseFailDistribution = $studentMarks->groupBy('course.course_title')->map(function ($marksPerCourse, $courseTitle) {
                return [
                    'course_title' => $courseTitle,
                    'total_students' => $marksPerCourse->count(),
                    'failed_students' => $marksPerCourse->where('grade_status', 'failed')->count(),
                ];
            })->values()->toArray();
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_course_fail_distribution'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'json_value', json_encode($courseFailDistribution)
            );


            // Total Number of Potential Resits
            $totalPotResits = $studentMarks->where('resit_status', 'high_resit_potential')->count();
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_total_number_of_potential_resits'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'integer_value', $totalPotResits
            );

            // Course with Number of Potential Resits Distribution
            $potResitsPerCourse = $studentMarks->where('resit_status', 'high_resit_potential')
                ->groupBy('course.course_title')
                ->map(function ($marks, $courseTitle) {
                    return [
                        'course_title' => $courseTitle,
                        'resit_count' => $marks->count(),
                    ];
                })
                ->values()
                ->toArray();

            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_course_potential_resit_distribution'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'json_value', json_encode($potResitsPerCourse)
            );

            // Course Score Distribution (Highest/Lowest Scores per course)
            $courseScoreDistribution = $this->analyzeCourseScores($studentMarks);
            $dataToInsert[] = $this->prepareStatData(
                $kpis->get('ca_exam_course_score_distribution'),
                $this->exam->id, $schoolBranchId, $schoolYear, $month, $year,
                'json_value', json_encode($courseScoreDistribution)
            );

            // --- 3. Batch Insert All Prepared Statistics ---
            $chunkSize = 500;
            foreach (array_chunk($dataToInsert, $chunkSize) as $chunk) {
                DB::table('school_ca_exam_stats')->insert($chunk);
            }

            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Failed to process CA Exam Statistics for Exam ID: {$this->exam->id}. Error: " . $e->getMessage());
            $this->fail($e);
        }
    }

    /**
     * Prepares an array of statistical data for insertion into the database.
     *
     * @param StatTypes|null $kpi The StatTypes model instance for the KPI.
     * @param string $examId The ID of the exam.
     * @param string $schoolBranchId The ID of the school branch.
     * @param string $schoolYear The school year.
     * @param int $month The month of the statistic.
     * @param int $year The year of the statistic.
     * @param string $valueType The type of value ('decimal_value', 'integer_value', 'json_value').
     * @param mixed $value The actual statistical value.
     * @return array The prepared data array.
     */
    private function prepareStatData(
        ?StatTypes $kpi,
        string $examId,
        string $schoolBranchId,
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

        // Assign the actual value to the correct column based on $valueType
        $data[$valueType] = $value;

        return $data;
    }

    /**
     * Calculates the distribution of grades across the entire exam based on student marks.
     *
     * @param \Illuminate\Support\Collection $studentMarks A collection of Marks models.
     * @param \Illuminate\Support\Collection $letterGrades A collection of LetterGrade models.
     * @return array An array of grade distributions, e.g., [['letter_grade' => 'A', 'count' => 10], ...].
     */
    public function gradesDistributionByExam($studentMarks, $letterGrades): array
    {
        // 1. Create a base distribution with all letter grades and 0 count
        // This ensures all defined grades are present, even if no student got them.
        $baseDistribution = $letterGrades->mapWithKeys(function ($gradeDefinition) {
            return [$gradeDefinition->letter_grade => [
                'letter_grade' => $gradeDefinition->letter_grade,
                'count' => 0,
            ]];
        });

        // 2. Count actual grade occurrences from student marks
        // pluck('grade') gets only the 'grade' values, filter() removes nulls, countBy() counts occurrences.
        $actualGradeCounts = $studentMarks
            ->pluck('grade')
            ->filter()
            ->countBy();

        // 3. Merge actual counts into the base distribution
        // Use map to iterate over the base distribution and update counts.
        $finalDistribution = $baseDistribution->map(function ($gradeData, $letterGradeKey) use ($actualGradeCounts) {
            // Get the count for this specific letter grade, defaulting to 0 if no students got it.
            $count = $actualGradeCounts->get($letterGradeKey, 0);
            $gradeData['count'] = $count; // Update the count in the copied array element
            return $gradeData; // Return the modified array element
        });

        // Convert the collection back to an array of values for the final output.
        return $finalDistribution->values()->toArray();
    }

    /**
     * Analyzes course-specific pass and fail statistics.
     *
     * @param \Illuminate\Support\Collection $studentMarks A collection of Marks models, with 'course' relationship loaded.
     * @return array An array containing 'pass_rates' and 'fail_rates' keyed by course title.
     */
    public function analyzeCourseStatistics($studentMarks): array
    {
        $courseGroupedMarks = $studentMarks->groupBy('course.course_title');

        $passRates = $courseGroupedMarks->mapWithKeys(function ($marks, $courseTitle) {
            $total = $marks->count();
            $passed = $marks->where('grade_status', 'passed')->count();
            $passRate = $total > 0 ? round(($passed / $total) * 100, 2) : 0.00;
            return [$courseTitle => $passRate];
        })->toArray();

        $failRates = $courseGroupedMarks->mapWithKeys(function ($marks, $courseTitle) {
            $total = $marks->count();
            $failed = $marks->where('grade_status', 'failed')->count();
            $failRate = $total > 0 ? round(($failed / $total) * 100, 2) : 0.00;
            return [$courseTitle => $failRate];
        })->toArray();

        return [
            'pass_rates' => $passRates,
            'fail_rates' => $failRates,
        ];
    }

    /**
     * Analyzes the highest and lowest scores for each course.
     *
     * @param \Illuminate\Support\Collection $studentMarks A collection of Marks models, with 'course' relationship loaded.
     * @return array An array of course score statistics, e.g., [['course_title' => 'Math', 'highest_score' => 95, 'lowest_score' => 30], ...].
     */
    public function analyzeCourseScores($studentMarks): array
    {
        $courseScores = $studentMarks->groupBy('course.course_title')->map(function ($marksPerCourse, $courseTitle) {
            $scores = $marksPerCourse->pluck('score');
            return [
                'course_title' => $courseTitle,
                'highest_score' => $scores->max(),
                'lowest_score' => $scores->min(),
            ];
        })->values()->toArray();

        return $courseScores;
    }
}
