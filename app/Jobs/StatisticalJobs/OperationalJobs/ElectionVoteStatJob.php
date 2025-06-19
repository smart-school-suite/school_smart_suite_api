<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\ElectionVotes; // Ensure this model is correctly configured
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ElectionVoteStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the election vote.
     * @var string
     */
    protected readonly string $voteId;

    /**
     * The ID of the school branch.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $voteId The ID of the election vote.
     * @param string $schoolBranchId The ID of the school branch.
     */
    public function __construct(string $voteId, string $schoolBranchId)
    {
        $this->voteId = $voteId;
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
            'total_votes_by_election',
            'total_election_votes_by_department',
            'total_election_votes_by_specialty'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $voteDetails = ElectionVotes::where("school_branch_id", $this->schoolBranchId)
            ->with(['election', 'student.department', 'student.specialty'])
            ->find($this->voteId);

        if (!$voteDetails) {
            Log::warning("Election vote with ID '{$this->voteId}' not found in school branch '{$this->schoolBranchId}'. Skipping election vote stats job.");
            return;
        }

        if (!$voteDetails->election) {
            Log::warning("Election not found for vote ID '{$this->voteId}'. Cannot calculate election-specific vote stats.");
            return;
        }

        $totalVotesKpi = $kpis->get('total_votes_by_election');
        if ($totalVotesKpi) {
            $this->upsertElectionVoteStat(
                $year,
                $month,
                $this->schoolBranchId,
                $totalVotesKpi,
                $voteDetails->election_id,
                null,
                null
            );
        } else {
            Log::warning("StatType 'total_votes_by_election' not found. Skipping total votes by election stat.");
        }


        $votesByDepartmentKpi = $kpis->get('total_election_votes_by_department');
        if ($votesByDepartmentKpi) {
            if ($voteDetails->student && $voteDetails->student->department_id) {
                $this->upsertElectionVoteStat(
                    $year,
                    $month,
                    $this->schoolBranchId,
                    $votesByDepartmentKpi,
                    $voteDetails->election_id,
                    $voteDetails->student->department_id,
                    null
                );
            } else {
                Log::info("Student or Department ID not found for vote '{$this->voteId}'. Skipping department-based vote count.");
            }
        } else {
            Log::warning("StatType 'total_election_votes_by_department' not found. Skipping department-based vote count.");
        }


        $votesBySpecialtyKpi = $kpis->get('total_election_votes_by_specialty');
        if ($votesBySpecialtyKpi) {
            if ($voteDetails->student && $voteDetails->student->specialty_id) {
                $this->upsertElectionVoteStat(
                    $year,
                    $month,
                    $this->schoolBranchId,
                    $votesBySpecialtyKpi,
                    $voteDetails->election_id,
                    null,
                    $voteDetails->student->specialty_id
                );
            } else {
                Log::info("Student or Specialty ID not found for vote '{$this->voteId}'. Skipping specialty-based vote count.");
            }
        } else {
            Log::warning("StatType 'total_election_votes_by_specialty' not found. Skipping specialty-based vote count.");
        }
    }

    /**
     * Inserts or updates an election vote statistic record, incrementing its integer_value.
     *
     * @param int $year The year for the statistic.
     * @param int $month The month for the statistic.
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI. (Non-nullable here as checked in handle)
     * @param string $electionId The ID of the election.
     * @param string|null $departmentId The department ID, if the stat is department-specific.
     * @param string|null $specialtyId The specialty ID, if the stat is specialty-specific.
     * @return void
     */
    private function upsertElectionVoteStat(
        int $year,
        int $month,
        string $schoolBranchId,
        StatTypes $kpi,
        string $electionId,
        ?string $departmentId = null,
        ?string $specialtyId = null
    ): void {

        $matchCriteria = [
            'year' => $year,
            'month' => $month,
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'election_id' => $electionId,
            'department_id' => $departmentId,
            'specialty_id' => $specialtyId,
        ];


        $existingStat = DB::table('election_vote_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('election_vote_stats')
                ->where($matchCriteria)
                ->increment('integer_value', 1, ['updated_at' => now()]);
            Log::info("Incremented existing election vote stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, election: {$electionId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        } else {

            DB::table('election_vote_stats')->insert([
                'id' => Str::uuid(),
                'year' => $year,
                'month' => $month,
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'election_id' => $electionId,
                'department_id' => $departmentId,
                'specialty_id' => $specialtyId,
                'integer_value' => 1,
                'decimal_value' => null,
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new election vote stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, election: {$electionId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        }
    }
}
