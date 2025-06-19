<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade for error reporting
use Illuminate\Support\Str;

class DepartmentStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the department (though not used directly for the 'total_number_of_departments' KPI,
     * it's kept as per original signature, perhaps for future expansion).
     * @var string
     */
    protected readonly string $departmentId;

    /**
     * The ID of the school branch where the department belongs.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $departmentId The ID of the department.
     * @param string $schoolBranchId The ID of the school branch.
     */
    public function __construct(string $departmentId, string $schoolBranchId)
    {
        $this->departmentId = $departmentId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $kpiNames = [
            'total_number_of_departments'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');


        $totalDepartmentsKpi = $kpis->get('total_number_of_departments');
        if (!$totalDepartmentsKpi) {
            Log::warning("StatType for 'total_number_of_departments' not found. Skipping department total count statistic for school: {$this->schoolBranchId}.");
            return;
        }

        $this->upsertDepartmentStat(
            $this->schoolBranchId,
            $totalDepartmentsKpi
        );
    }

    /**
     * Inserts or updates the total department count statistic.
     *
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI. (No longer nullable here as checked in handle)
     * @return void
     */
    private function upsertDepartmentStat(string $schoolBranchId, StatTypes $kpi): void
    {

        $matchCriteria = [
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
        ];


        $existingStat = DB::table('department_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('department_stats')
                ->where($matchCriteria)
                ->increment('integer_value', 1, ['updated_at' => now()]);
            Log::info("Incremented existing stat for 'total_number_of_departments' for school: {$schoolBranchId}.");
        } else {

            DB::table('department_stats')->insert([
                'id' => Str::uuid(),
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'integer_value' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new stat for 'total_number_of_departments' for school: {$schoolBranchId}.");
        }
    }
}
