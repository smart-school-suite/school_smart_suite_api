<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\StatTypes;
use App\Models\ElectionApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class ElectionApplicationApprovalStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $applicationId;
    protected $schoolBranchId;
    public function __construct(string $applicationId, string $schoolBranchId)
    {
        $this->applicationId = $applicationId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       $year = now()->year;
        $kpiNames = [
            'total_application_acceptance_count',
        ];
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');
        $applicationDetails = ElectionApplication::where("school_branch_id", $this->schoolBranchId)
                                                   ->where("application_status",  'approved')
                                                   ->find($this->applicationId);
        $acceptanceCountKpi = $kpis->get('total_application_acceptance_count');
        if($applicationDetails){
            $this->upsertElectionApplicationStat(
                $this->schoolBranchId,
                $acceptanceCountKpi,
                $year
            );
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
    private function upsertElectionApplicationStat(
        string $schoolBranchId,
        ?StatTypes $kpi,
        int $year
    ): void {
        if (!$kpi) {
            Log::warning("StatType for KPI not found. Cannot record course stat for school: {$schoolBranchId}.");
            return;
        }

        $matchCriteria = [
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'year' => $year
        ];


        $existingStat = DB::table('election_application_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('election_application_stats')
                ->where($matchCriteria)
                ->increment('integer_value', 1, ['updated_at' => now()]);
        } else {

            DB::table('election_application_stats')->insert([
                'id' => Str::uuid(),
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'year' => $year,
                'integer_value' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
