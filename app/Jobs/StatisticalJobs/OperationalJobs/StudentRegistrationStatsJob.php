<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\StatTypes;
use App\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Str; // Import Str facade

class StudentRegistrationStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the student for whom statistics are being calculated.
     * @var string
     */
    protected readonly string $studentId;

    /**
     * The ID of the school branch where the student is registered.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $studentId The ID of the student.
     * @param string $schoolBranchId The ID of the school branch.
     */
    public function __construct(string $studentId, string $schoolBranchId)
    {
        $this->studentId = $studentId;
        $this->schoolBranchId = $schoolBranchId;
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

        $student = Student::where("school_branch_id", $this->schoolBranchId)->find($this->studentId);

        if (!$student) {
            Log::warning("Student with ID '{$this->studentId}' not found in school branch '{$this->schoolBranchId}'. Skipping student registration stats job.");
            return;
        }

        $kpiNames = [
            "registered_students_count_over_time",
            "female_registered_student_count_over_time",
            "male_registered_student_count_over_time",
            "specialty_registration_count_over_time",
            "department_registration_count_over_time",
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');


        $this->upsertStudentStat(
            $year,
            $month,
            $kpis->get('registered_students_count_over_time'),
            $this->schoolBranchId,
            null,
            null
        );


        if ($student->gender === 'female') {
            $this->upsertStudentStat(
                $year,
                $month,
                $kpis->get('female_registered_student_count_over_time'),
                $this->schoolBranchId,
                null,
                null
            );
        } elseif ($student->gender === 'male') {
            $this->upsertStudentStat(
                $year,
                $month,
                $kpis->get('male_registered_student_count_over_time'),
                $this->schoolBranchId,
                null,
                null
            );
        }


        if ($student->specialty_id) {
            $this->upsertStudentStat(
                $year,
                $month,
                $kpis->get('specialty_registration_count_over_time'),
                $this->schoolBranchId,
                $student->specialty_id,
                null
            );
        } else {
            Log::info("Student with ID '{$this->studentId}' has no specialty ID. Skipping specialty registration stat.");
        }

        if ($student->department_id) {
            $this->upsertStudentStat(
                $year,
                $month,
                $kpis->get('department_registration_count_over_time'),
                $this->schoolBranchId,
                null,
                $student->department_id
            );
        } else {
            Log::info("Student with ID '{$this->studentId}' has no department ID. Skipping department registration stat.");
        }
    }

    /**
     * Inserts or updates a student statistic record, incrementing its integer_value.
     *
     * @param int $year The year for the statistic.
     * @param int $month The month for the statistic.
     * @param StatTypes|null $kpi The StatTypes model instance for the KPI, or null if not found.
     * @param string $schoolBranchId The ID of the school branch.
     * @param string|null $specialtyId The specialty ID, if the stat is specialty-specific.
     * @param string|null $departmentId The department ID, if the stat is department-specific.
     * @return void
     */
    private function upsertStudentStat(
        int $year,
        int $month,
        ?StatTypes $kpi,
        string $schoolBranchId,
        ?string $specialtyId = null,
        ?string $departmentId = null
    ): void {
        if (!$kpi) {
            Log::warning("StatType for KPI not found. Cannot record student stat for year: {$year}, month: {$month}, school: {$schoolBranchId}.");
            return;
        }

        $matchCriteria = [
            'year' => $year,
            'month' => $month,
            'stat_type_id' => $kpi->id,
            'school_branch_id' => $schoolBranchId,
            'specialty_id' => $specialtyId,
            'department_id' => $departmentId,
        ];

        $existingStat = DB::table('student_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('student_stats')
                ->where($matchCriteria)
                ->increment('integer_value', 1, ['updated_at' => now()]);
            Log::info("Incremented existing student stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, year: {$year}, month: {$month}, specialty: {$specialtyId}, department: {$departmentId}.");
        } else {

            DB::table('student_stats')->insert([
                'id' => Str::uuid(),
                'year' => $year,
                'month' => $month,
                'stat_type_id' => $kpi->id,
                'school_branch_id' => $schoolBranchId,
                'specialty_id' => $specialtyId,
                'department_id' => $departmentId,
                'integer_value' => 1,
                'decimal_value' => null,
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new student stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, year: {$year}, month: {$month}, specialty: {$specialtyId}, department: {$departmentId}.");
        }
    }
}
