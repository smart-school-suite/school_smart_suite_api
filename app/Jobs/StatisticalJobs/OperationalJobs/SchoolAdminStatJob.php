<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\Schooladmin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\StatTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class SchoolAdminStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $schoolBranchId;
    protected $schoolAdminId;
    public function __construct(string $schoolBranchId, string $schoolAdminId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->schoolAdminId = $schoolAdminId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $year = now()->year;
        $kpiNames = [
            'total_school_admin'
        ];

         $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');
         $schoolAdmin = Schooladmin::where("school_branch_id", $this->schoolBranchId)
                      ->find($this->schoolAdminId);
         $schoolAdminCount = $kpis->get('total_school_admin');
         if($schoolAdmin){
            if (!$schoolAdminCount) {
            Log::warning("StatType for KPI not found. Cannot record school admin Count stat for school: {$this->schoolBranchId}.");
            return;
        }
            $this->upsertSchoolAdminStat(
                $this->schoolBranchId,
                $schoolAdminCount,
                $year
            );
         }else{
          Log::warning("StatType 'total_school_admin' not found. Skipping School Admin Count.");
         }

    }

         /**
     * Inserts or updates a course statistic record, incrementing its integer_value.
     *
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes|null $kpi The StatTypes model instance for the KPI, or null if not found.
     * @param string|null $specialtyId The specialty ID, if the stat is specialty-specific.
     * @param string|null $departmentId The department ID, if the stat is department-specific.
     * @return void
     */
    private function upsertSchoolAdminStat(
        string $schoolBranchId,
        ?StatTypes $kpi,
        int $year
    ): void {


        $matchCriteria = [
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'year' => $year
        ];


        $existingStat = DB::table('school_admin_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('school_admin_stats')
                ->where($matchCriteria)
                ->increment('integer_value', 1, ['updated_at' => now()]);
        } else {

            DB::table('school_admin_stats')->insert([
                'id' => Str::uuid(),
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'integer_value' => 1,
                'year' => $year,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
