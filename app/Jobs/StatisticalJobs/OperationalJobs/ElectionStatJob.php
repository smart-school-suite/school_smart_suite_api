<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\Elections; // Ensure this model is used and correctly configured
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Str;

class ElectionStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the election.
     * @var string
     */
    protected readonly string $electionId;

    /**
     * The ID of the school branch.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $electionId The ID of the election.
     * @param string $schoolBranchId The ID of the school branch.
     */
    public function __construct(string $electionId, string $schoolBranchId)
    {
        $this->electionId = $electionId;
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

        $kpiNames = [
            'total_election_count',
            'total_election_type_count_by_election'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        try {
            $electionDetails = Elections::findOrFail($this->electionId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Election with ID '{$this->electionId}' not found. Skipping ElectionStatJob.");
            return;
        }


        $totalElectionKpi = $kpis->get("total_election_count");
        if ($totalElectionKpi) {
            $this->upsertElectionStat(
                $year,
                $month,
                $this->schoolBranchId,
                $totalElectionKpi,
                null
            );
        } else {
            Log::warning("StatType 'total_election_count' not found. Skipping general election count.");
        }


        $electionTypeKpi = $kpis->get('total_election_type_count_by_election');
        if ($electionTypeKpi) {
            if ($electionDetails->election_type_id) {
                $this->upsertElectionStat(
                    $year,
                    $month,
                    $this->schoolBranchId,
                    $electionTypeKpi,
                    $electionDetails->election_type_id
                );
            } else {
                Log::info("Election '{$this->electionId}' has no election_type_id. Skipping election type count.");
            }
        } else {
            Log::warning("StatType 'total_election_type_count_by_election' not found. Skipping election type count.");
        }
    }

    /**
     * Inserts or updates an election statistic record, incrementing its integer_value.
     *
     * @param int $year The year for the statistic.
     * @param int $month The month for the statistic.
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI. (Non-nullable here as checked in handle)
     * @param string|null $electionTypeId The ID of the election type, if the stat is type-specific.
     * @return void
     */
    private function upsertElectionStat(
        int $year,
        int $month,
        string $schoolBranchId,
        StatTypes $kpi,
        ?string $electionTypeId = null
    ): void {

        $matchCriteria = [
            'year' => $year,
            'month' => $month,
            'stat_type_id' => $kpi->id,
            'school_branch_id' => $schoolBranchId,
            'election_type_id' => $electionTypeId,
        ];

        $existingStat = DB::table('election_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('election_stats')
                ->where($matchCriteria)
                ->increment('integer_value', 1, ['updated_at' => now()]);
            Log::info("Incremented existing election stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, year: {$year}, month: {$month}, election_type_id: {$electionTypeId}.");
        } else {

            DB::table('election_stats')->insert([
                'id' => Str::uuid(),
                'year' => $year,
                'month' => $month,
                'stat_type_id' => $kpi->id,
                'school_branch_id' => $schoolBranchId,
                'election_type_id' => $electionTypeId,
                'integer_value' => 1,
                'decimal_value' => null,
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new election stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, year: {$year}, month: {$month}, election_type_id: {$electionTypeId}.");
        }
    }
}
