<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\Teacher;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Str; // Import Str facade

class TeacherRegistrationStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the teacher for whom statistics are being calculated.
     * @var string
     */
    protected readonly string $teacherId;

    /**
     * The ID of the school branch where the teacher is registered.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $teacherId The ID of the teacher.
     * @param string $schoolBranchId The ID of the school branch.
     */
    public function __construct(string $teacherId, string $schoolBranchId)
    {
        $this->teacherId = $teacherId;
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

        $teacher = Teacher::where("school_branch_id", $this->schoolBranchId)->find($this->teacherId);

        if (!$teacher) {
            Log::warning("Teacher with ID '{$this->teacherId}' not found in school branch '{$this->schoolBranchId}'. Skipping teacher registration stats job.");
            return;
        }

        $kpiNames = [
            "registered_teachers_count_over_time",
            "female_registered_teachers_count_over_time",
            "male_registered_teachers_count_over_time",
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');


        $this->upsertTeacherStat(
            $year,
            $month,
            $kpis->get('registered_teachers_count_over_time'),
            $this->schoolBranchId
        );

        if ($teacher->gender === 'male') {
            $this->upsertTeacherStat(
                $year,
                $month,
                $kpis->get('male_registered_teachers_count_over_time'),
                $this->schoolBranchId
            );
        } elseif ($teacher->gender === 'female') {
            $this->upsertTeacherStat(
                $year,
                $month,
                $kpis->get('female_registered_teachers_count_over_time'),
                $this->schoolBranchId
            );
        }
    }

    /**
     * Inserts or updates a teacher statistic record, incrementing its integer_value.
     *
     * @param int $year The year for the statistic.
     * @param int $month The month for the statistic.
     * @param StatTypes|null $kpi The StatTypes model instance for the KPI, or null if not found.
     * @param string $schoolBranchId The ID of the school branch.
     * @return void
     */
    private function upsertTeacherStat(
        int $year,
        int $month,
        ?StatTypes $kpi,
        string $schoolBranchId
    ): void {
        if (!$kpi) {
            Log::warning("StatType for KPI not found. Cannot record teacher stat for year: {$year}, month: {$month}, school: {$schoolBranchId}.");
            return;
        }


        $matchCriteria = [
            'year' => $year,
            'month' => $month,
            'stat_type_id' => $kpi->id,
            'school_branch_id' => $schoolBranchId,
        ];

        $existingStat = DB::table('teacher_stats')->where($matchCriteria)->first();
        if ($existingStat) {
            DB::table('teacher_stats')
                ->where($matchCriteria)
                ->increment('integer_value', 1, ['updated_at' => now()]);
            Log::info("Incremented existing teacher stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, year: {$year}, month: {$month}.");
        } else {
            DB::table('teacher_stats')->insert([
                'id' => Str::uuid(),
                'year' => $year,
                'month' => $month,
                'stat_type_id' => $kpi->id,
                'school_branch_id' => $schoolBranchId,
                'integer_value' => 1,
                'decimal_value' => null,
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new teacher stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, year: {$year}, month: {$month}.");
        }
    }
}
