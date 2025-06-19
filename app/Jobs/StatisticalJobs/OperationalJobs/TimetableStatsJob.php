<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class TimetableStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the school branch.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * The ID of the specialty.
     * @var string
     */
    protected readonly string $specialtyId;

    /**
     * The ID of the student batch.
     * @var string
     */
    protected readonly string $studentBatchId;

    /**
     * The ID of the semester.
     * @var string
     */
    protected readonly string $semesterId;

    /**
     * Create a new job instance.
     *
     * @param string $schoolBranchId The ID of the school branch.
     * @param string $specialtyId The ID of the specialty.
     * @param string $studentBatchId The ID of the student batch.
     * @param string $semesterId The ID of the semester.
     */
    public function __construct(
        string $schoolBranchId,
        string $specialtyId,
        string $studentBatchId,
        string $semesterId
    ) {
        $this->schoolBranchId = $schoolBranchId;
        $this->specialtyId = $specialtyId;
        $this->studentBatchId = $studentBatchId;
        $this->semesterId = $semesterId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $year = now()->year;
        $month = now()->month;

        $kpiNames = [
            "total_number_of_courses_per_semester",
            "total_number_of_courses_per_specialty",
            "total_number_of_courses_per_teacher",
            "total_average_course_per_day"
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $timetable = DB::table('timetables')
            ->where("school_branch_id", $this->schoolBranchId)
            ->where("semester_id", $this->semesterId)
            ->where("specialty_id", $this->specialtyId)
            ->where("student_batch_id", $this->studentBatchId)
            ->get();


        if ($timetable->isEmpty()) {
            Log::info("No timetable data found for schoolBranchId: {$this->schoolBranchId}, specialtyId: {$this->specialtyId}, studentBatchId: {$this->studentBatchId}, semesterId: {$this->semesterId}. Skipping timetable stats job.");
            return;
        }

        $this->totalCoursesCountBySemester(
            $year,
            $month,
            $this->semesterId,
            $kpis->get('total_number_of_courses_per_semester'),
            $this->schoolBranchId,
            $timetable
        );

        $this->totalCoursePerSpecialty(
            $year,
            $month,
            $this->semesterId,
            $this->specialtyId,
            $kpis->get('total_number_of_courses_per_specialty'),
            $this->schoolBranchId,
            $timetable
        );

        $this->averageCoursePerDay(
            $year,
            $month,
            $this->semesterId,
            $kpis->get("total_average_course_per_day"),
            $this->schoolBranchId,
            $timetable
        );

        $this->averageCoursePerTeacher(
            $year,
            $month,
            $this->semesterId,
            $kpis->get('total_number_of_courses_per_teacher'),
            $this->schoolBranchId,
            $timetable
        );
    }

    /**
     * Stores or updates a timetable-related statistic in the database.
     * This method now acts as an upsert.
     *
     * @param int $year The year of the statistic.
     * @param int $month The month of the statistic.
     * @param string $semesterId The ID of the semester.
     * @param StatTypes|null $kpi The StatTypes model instance for the KPI, or null if not found.
     * @param string $schoolBranchId The ID of the school branch.
     * @param string|null $specialtyId The ID of the specialty, if applicable.
     * @param int|null $integerValue The integer value of the statistic.
     * @param float|null $decimalValue The decimal value of the statistic.
     * @param string|null $jsonValue The JSON value of the statistic.
     * @return void
     */
    private function storeStat(
        int $year,
        int $month,
        string $semesterId,
        ?StatTypes $kpi,
        string $schoolBranchId,
        ?string $specialtyId,
        ?int $integerValue,
        ?float $decimalValue,
        ?string $jsonValue
    ): void {
        if (!$kpi) {
            Log::warning("StatType not found for KPI. Skipping statistic storage.");
            return;
        }

        $matchCriteria = [
            'year' => $year,
            'month' => $month,
            'stat_type_id' => $kpi->id,
            'school_branch_id' => $schoolBranchId,
            'semester_id' => $semesterId,
            'specialty_id' => $specialtyId,
        ];

        $updateValues = [
            'integer_value' => $integerValue,
            'decimal_value' => $decimalValue,
            'json_value' => $jsonValue,
            'updated_at' => now(),
        ];

        $existingStat = DB::table('class_timetable_stats')->where($matchCriteria)->first();

        if ($existingStat) {
            DB::table('class_timetable_stats')
                ->where($matchCriteria)
                ->update($updateValues);
            Log::info("Updated existing timetable stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, semester: {$semesterId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        } else {
            DB::table('class_timetable_stats')->insert(array_merge($matchCriteria, $updateValues, [
                'id' => Str::uuid(),
                'created_at' => now(),
            ]));
            Log::info("Created new timetable stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, semester: {$semesterId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        }
    }

    /**
     * Calculates and stores the total number of courses for a semester.
     *
     * @param int $year
     * @param int $month
     * @param string $semesterId
     * @param StatTypes|null $kpi
     * @param string $schoolBranchId
     * @param Collection $timetable
     * @return void
     */
    private function totalCoursesCountBySemester(
        int $year,
        int $month,
        string $semesterId,
        ?StatTypes $kpi,
        string $schoolBranchId,
        Collection $timetable
    ): void {
        if (!$kpi) {
            Log::warning("KPI 'total_number_of_courses_per_semester' not found. Skipping stat calculation.");
            return;
        }

        $this->storeStat(
            $year,
            $month,
            $semesterId,
            $kpi,
            $schoolBranchId,
            null,
            $timetable->count(),
            null,
            null
        );
    }

    /**
     * Calculates and stores the total number of courses per specialty.
     *
     * @param int $year
     * @param int $month
     * @param string $semesterId
     * @param string $specialtyId
     * @param StatTypes|null $kpi
     * @param string $schoolBranchId
     * @param Collection $timetable
     * @return void
     */
    private function totalCoursePerSpecialty(
        int $year,
        int $month,
        string $semesterId,
        string $specialtyId,
        ?StatTypes $kpi,
        string $schoolBranchId,
        Collection $timetable
    ): void {
        if (!$kpi) {
            Log::warning("KPI 'total_number_of_courses_per_specialty' not found. Skipping stat calculation.");
            return;
        }

        $totalCoursesInSpecialty = $timetable->count();


        $this->storeStat(
            $year,
            $month,
            $semesterId,
            $kpi,
            $schoolBranchId,
            $specialtyId,
            $totalCoursesInSpecialty,
            null,
            null
        );
    }

    /**
     * Calculates and stores the average number of courses per day.
     *
     * @param int $year
     * @param int $month
     * @param string $semesterId
     * @param StatTypes|null $kpi
     * @param string $schoolBranchId
     * @param Collection $timetable
     * @return void
     */
    private function averageCoursePerDay(
        int $year,
        int $month,
        string $semesterId,
        ?StatTypes $kpi,
        string $schoolBranchId,
        Collection $timetable
    ): void {
        if (!$kpi) {
            Log::warning("KPI 'total_average_course_per_day' not found. Skipping stat calculation.");
            return;
        }

        $courseCount = $timetable->count();
        $daysPerWeek = 7;
        $average = ($daysPerWeek > 0) ? (float) $courseCount / $daysPerWeek : 0.0;

        $this->storeStat(
            $year,
            $month,
            $semesterId,
            $kpi,
            $schoolBranchId,
            null,
            null,
            $average,
            null
        );
    }

    /**
     * Calculates and stores the average number of courses per teacher.
     *
     * @param int $year
     * @param int $month
     * @param string $semesterId
     * @param StatTypes|null $kpi
     * @param string $schoolBranchId
     * @param Collection $timetable
     * @return void
     */
    private function averageCoursePerTeacher(
        int $year,
        int $month,
        string $semesterId,
        ?StatTypes $kpi,
        string $schoolBranchId,
        Collection $timetable
    ): void {
        if (!$kpi) {
            Log::warning("KPI 'total_number_of_courses_per_teacher' not found. Skipping stat calculation.");
            return;
        }

        $teacherCount = $timetable->pluck('teacher_id')->unique()->count();
        $courseCount = $timetable->pluck('course_id')->unique()->count();

        $average = 0.0;
        if ($teacherCount > 0) {
            $average = $courseCount / (float) $teacherCount;
        } else {
            Log::warning("No unique teachers found in the timetable for semester {$semesterId}, specialty {$this->specialtyId}. Average course per teacher cannot be calculated.");
        }

        $this->storeStat(
            $year,
            $month,
            $semesterId,
            $kpi,
            $schoolBranchId,
            null,
            null,
            $average,
            null
        );
    }
}
