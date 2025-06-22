<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\StatTypes;
use App\Models\ElectionResults;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Str;

class ElectionWinnerStatJob implements ShouldQueue
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
            'election_role_winner_total_vote',
            'election_role_winner_by_department',
            'election_role_winner_by_specialty',
            'election_role_winner_by_male_gender',
            'election_role_Winner_by_female_gender'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');


        $electionWinners = ElectionResults::where("election_id", $this->electionId)
            ->where("school_branch_id", $this->schoolBranchId)
            ->where("election_status", "won")
            ->with(['Elections', 'electionCandidate.student.department', 'electionCandidate.student.specialty'])
            ->get();

        if ($electionWinners->isEmpty()) {
            Log::info("No election winners found for election ID: {$this->electionId} in school branch: {$this->schoolBranchId}. Skipping stats calculation.");
            return;
        }

        foreach ($electionWinners as $winner) {

            if (!$winner->Elections || !$winner->electionCandidate || !$winner->electionCandidate->student) {
                Log::warning("Skipping winner stat for ElectionResult ID {$winner->id} due to missing election, candidate, or student data.");
                continue;
            }

            $electionTypeId = $winner->Elections->election_type_id;
            $electionRoleId = $winner->position_id;
            $student = $winner->electionCandidate->student;
            $totalVoteKpi = $kpis->get('election_role_winner_total_vote');
            if ($totalVoteKpi) {
                $this->upsertElectionStat(
                    $year,
                    $month,
                    $this->schoolBranchId,
                    $totalVoteKpi,
                    $electionTypeId,
                    $electionRoleId,
                    (int) $winner->vote_count,
                    null,
                    null,
                    null
                );
            } else {
                Log::warning("StatType 'election_role_winner_total_vote' not found. Skipping total winner vote count for election type {$electionTypeId}, role {$electionRoleId}.");
            }


            $byDepartmentKpi = $kpis->get('election_role_winner_by_department');
            if ($byDepartmentKpi) {
                if ($student->department_id) {
                    $this->upsertElectionStat(
                        $year,
                        $month,
                        $this->schoolBranchId,
                        $byDepartmentKpi,
                        $electionTypeId,
                        $electionRoleId,
                        1,
                        null,
                        $student->department_id,
                        null
                    );
                } else {
                    Log::info("Winner {$winner->id} (student {$student->id}) has no department ID. Skipping department stat.");
                }
            } else {
                Log::warning("StatType 'election_role_winner_by_department' not found. Skipping department-based winner count.");
            }

            $bySpecialtyKpi = $kpis->get('election_role_winner_by_specialty');
            if ($bySpecialtyKpi) {
                if ($student->specialty_id) {
                    $this->upsertElectionStat(
                        $year,
                        $month,
                        $this->schoolBranchId,
                        $bySpecialtyKpi,
                        $electionTypeId,
                        $electionRoleId,
                        1,
                        null,
                        null,
                        $student->specialty_id
                    );
                } else {
                    Log::info("Winner {$winner->id} (student {$student->id}) has no specialty ID. Skipping specialty stat.");
                }
            } else {
                Log::warning("StatType 'election_role_winner_by_specialty' not found. Skipping specialty-based winner count.");
            }

            $byMaleGenderKpi = $kpis->get('election_role_winner_by_male_gender');
            if ($byMaleGenderKpi) {
                if ($student->gender === 'male') {
                    $this->upsertElectionStat(
                        $year,
                        $month,
                        $this->schoolBranchId,
                        $byMaleGenderKpi,
                        $electionTypeId,
                        $electionRoleId,
                        1
                    );
                }
            } else {
                Log::warning("StatType 'election_role_winner_by_male_gender' not found. Skipping male winner count.");
            }


            $byFemaleGenderKpi = $kpis->get('election_role_Winner_by_female_gender');
            if ($byFemaleGenderKpi) {
                if ($student->gender === 'female') {
                    $this->upsertElectionStat(
                        $year,
                        $month,
                        $this->schoolBranchId,
                        $byFemaleGenderKpi,
                        $electionTypeId,
                        $electionRoleId,
                        1
                    );
                }
            } else {
                Log::warning("StatType 'election_role_Winner_by_female_gender' not found. Skipping female winner count.");
            }
        }
    }

    /**
     * Inserts or updates an election winner statistic record.
     *
     * @param int $year The year for the statistic.
     * @param int $month The month for the statistic.
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI.
     * @param string $electionTypeId The ID of the election type.
     * @param string $electionRoleId The ID of the election role/position.
     * @param int $integerValue The integer value to add/set for the stat. Default is 1 for counts.
     * @param float|null $decimalValue The decimal value for the stat.
     * @param string|null $departmentId The department ID, if the stat is department-specific.
     * @param string|null $specialtyId The specialty ID, if the stat is specialty-specific.
     * @return void
     */
    private function upsertElectionStat(
        int $year,
        int $month,
        string $schoolBranchId,
        StatTypes $kpi,
        string $electionTypeId,
        string $electionRoleId,
        int $integerValue = 1,
        ?float $decimalValue = null,
        ?string $departmentId = null,
        ?string $specialtyId = null
    ): void {

        $matchCriteria = [
            'year' => $year,
            'month' => $month,
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'election_type_id' => $electionTypeId,
            'election_role_id' => $electionRoleId,
            'department_id' => $departmentId,
            'specialty_id' => $specialtyId,
        ];


        $existingStat = DB::table('election_winner_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('election_winner_stats')
                ->where($matchCriteria)
                ->update([
                    'integer_value' => DB::raw("COALESCE(integer_value, 0) + {$integerValue}"),
                    'decimal_value' => $decimalValue,
                    'updated_at' => now(),
                ]);
            Log::info("Incremented existing election winner stat for KPI '{$kpi->program_name}' (value: {$integerValue}) for school: {$schoolBranchId}, election_type: {$electionTypeId}, role: {$electionRoleId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        } else {
            DB::table('election_winner_stats')->insert([
                'id' => Str::uuid(),
                'year' => $year,
                'month' => $month,
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'election_type_id' => $electionTypeId,
                'election_role_id' => $electionRoleId,
                'department_id' => $departmentId,
                'specialty_id' => $specialtyId,
                'integer_value' => $integerValue,
                'decimal_value' => $decimalValue,
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new election winner stat for KPI '{$kpi->program_name}' (value: {$integerValue}) for school: {$schoolBranchId}, election_type: {$electionTypeId}, role: {$electionRoleId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        }
    }
}
