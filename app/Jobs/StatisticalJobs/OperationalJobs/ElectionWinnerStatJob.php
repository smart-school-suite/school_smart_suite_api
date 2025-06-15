<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ElectionWinnerStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $electionId;
    protected $schoolBranchId;
    public function __construct(string $electionId, string $schoolBranchId)
    {
        $this->electionId = $electionId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $electionId = $this->electionId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;
        $kpiNames = [
            'election_role_winner_total_vote',
            'election_role_winner_by_department',
            'election_role_winner_by_specialty',
            'election_role_winner_by_male_gender',
            'election_role_Winner_by_female_gender'
        ];
        $electionWinners = DB::table('elections_results')
            ->where("election_id", $electionId)
            ->where("school_branch_id", $schoolBranchId)
            ->where("status", "won")
            ->with(['Elections', 'electionCandidate.student'])
            ->get();
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $this->electionRoleWinnerTotalVote(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('election_role_winner_total_vote'),
            $electionWinners
        );
        $this->electionRoleWinnerByDepartment(
            $schoolBranchId,
            $kpis->get('election_role_winner_by_department'),
            $electionWinners
        );
        $this->electionRoleWinnerBySpecialty(
            $schoolBranchId,
            $kpis->get('election_role_winner_by_specialty'),
            $electionWinners
        );
        $this->electionRoleWinnerByMaleGender(
            $schoolBranchId,
            $kpis->get('election_role_winner_by_male_gender'),
            $electionWinners
        );
        $this->electionRoleWinnerByFemaleGender(
            $schoolBranchId,
            $kpis->get('election_role_Winner_by_female_gender'),
            $electionWinners
        );
    }

    private function electionRoleWinnerTotalVote($year, $month,  $schoolBranchId, $kpi, $electionWinners)
    {
        $winnersListToBeInserted = [];
        foreach ($electionWinners as $electionWinner) {
            $winnersListToBeInserted = [
                'id' => Str::uuid(),
                'stat_type_id' => $kpi->id,
                'election_type_id' => $electionWinner->election->election_type_id,
                'election_role_id' => $electionWinner->position_id,
                'decimal_value' => null,
                'interger_value' => $electionWinner->vote_count,
                'json_value' => null,
                'school_branch_id' => $schoolBranchId,
                'month' => $month,
                'year' => $year,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('election_winner_stats')->insert($winnersListToBeInserted);
    }
    private function electionRoleWinnerByDepartment($schoolBranchId, $kpi, $electionWinners)
    {
        foreach ($electionWinners as $electionWinner) {
            $roleWinnerByDepartment = DB::table('election_winners_by_department')
                ->where("election_type_id", $electionWinner->election->election_type_id)
                ->where("election_role_id", $electionWinner->position_id)
                ->where("stat_type_id", $kpi->id)
                ->where("school_branch_id", $schoolBranchId)
                ->where("department_id", $electionWinners->electionCandidate->student->department_id)
                ->first();
            if ($roleWinnerByDepartment) {
                $roleWinnerByDepartment->interger_value++;
                $roleWinnerByDepartment->save();
            }

            DB::table('election_winner_stats')->insert([
                'id' => Str::uuid(),
                'stat_type_id' => $kpi->id,
                'election_type_id' => $electionWinner->election->election_type_id,
                'election_role_id' => $electionWinner->position_id,
                'department_id' => $electionWinners->electionCandidate->student->department_id,
                'decimal_value' => null,
                'interger_value' => 1,
                'json_value' => null,
                'school_branch_id' => $schoolBranchId,
                'month' => null,
                'year' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    private function electionRoleWinnerBySpecialty($schoolBranchId, $kpi, $electionWinners)
    {
        foreach ($electionWinners as $electionWinner) {
            $roleWinnerByDepartment = DB::table('election_winners_by_specialty')
                ->where("election_type_id", $electionWinner->election->election_type_id)
                ->where("election_role_id", $electionWinner->position_id)
                ->where("stat_type_id", $kpi->id)
                ->where("school_branch_id", $schoolBranchId)
                ->where("specialty_id", $electionWinners->electionCandidate->student->specialty_id)
                ->first();
            if ($roleWinnerByDepartment) {
                $roleWinnerByDepartment->interger_value++;
                $roleWinnerByDepartment->save();
            }

            DB::table('election_winner_stats')->insert([
                'id' => Str::uuid(),
                'stat_type_id' => $kpi->id,
                'election_type_id' => $electionWinner->election->election_type_id,
                'election_role_id' => $electionWinner->position_id,
                'specialty_id' => $electionWinners->electionCandidate->student->specialty_id,
                'decimal_value' => null,
                'interger_value' => 1,
                'json_value' => null,
                'school_branch_id' => $schoolBranchId,
                'month' => null,
                'year' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    private function electionRoleWinnerByMaleGender($schoolBranchId, $kpi, $electionWinners){
        foreach ($electionWinners as $electionWinner) {
            $roleWinnerByDepartment = DB::table('election_winners_stat_by_gender')
                ->where("election_type_id", $electionWinner->election->election_type_id)
                ->where("election_role_id", $electionWinner->position_id)
                ->where("stat_type_id", $kpi->id)
                ->where("school_branch_id", $schoolBranchId)
                ->first();
            if ($roleWinnerByDepartment) {
                $roleWinnerByDepartment->interger_value++;
                $roleWinnerByDepartment->save();
            }

            DB::table('election_winner_stats')->insert([
                'id' => Str::uuid(),
                'stat_type_id' => $kpi->id,
                'election_type_id' => $electionWinner->election->election_type_id,
                'election_role_id' => $electionWinner->position_id,
                'decimal_value' => null,
                'interger_value' => 1,
                'json_value' => null,
                'school_branch_id' => $schoolBranchId,
                'month' => null,
                'year' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    private function electionRoleWinnerByFemaleGender($schoolBranchId, $kpi, $electionWinners){
        foreach ($electionWinners as $electionWinner) {
            $roleWinnerByDepartment = DB::table('election_winners_stat_by_gender')
                ->where("election_type_id", $electionWinner->election->election_type_id)
                ->where("election_role_id", $electionWinner->position_id)
                ->where("stat_type_id", $kpi->id)
                ->where("school_branch_id", $schoolBranchId)
                ->first();
            if ($roleWinnerByDepartment) {
                $roleWinnerByDepartment->interger_value++;
                $roleWinnerByDepartment->save();
            }

            DB::table('election_winner_stats')->insert([
                'id' => Str::uuid(),
                'stat_type_id' => $kpi->id,
                'election_type_id' => $electionWinner->election->election_type_id,
                'election_role_id' => $electionWinner->position_id,
                'decimal_value' => null,
                'interger_value' => 1,
                'json_value' => null,
                'school_branch_id' => $schoolBranchId,
                'month' => null,
                'year' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
