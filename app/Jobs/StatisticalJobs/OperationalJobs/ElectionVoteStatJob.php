<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\ElectionVotes;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ElectionVoteStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $voteId;
    protected $schoolBranchId;
    public function __construct(string $voteId, string $schoolBranchId)
    {
        $this->voteId = $voteId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $voteId= $this->voteId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;

        $kpiNames = [
            'total_votes_by_election',
            'total_election_votes_by_department',
            'total_election_votes_by_specialty'
        ];

        $voteDetails = ElectionVotes::where("school_branch_id", $schoolBranchId)
                                    ->with(['student', 'election'])
                                    ->find($voteId);
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $this->totalVotesByElection(
             $year,
             $month,
             $schoolBranchId,
             $voteDetails,
             $kpis->get('total_votes_by_election')
        );
        $this->totalElectionVotesByDepartment(
            $year,
             $month,
             $schoolBranchId,
             $voteDetails,
             $kpis->get('total_election_votes_by_department')
        );
        $this->totalElectionVotesBySpecialty(
            $year,
             $month,
             $schoolBranchId,
             $voteDetails,
             $kpis->get('total_election_votes_by_specialty')
        );
    }

    private function totalVotesByElection($year, $month, $schoolBranchId, $voteDetails, $kpi){
          $electionStat = DB::table('election_vote_stats')
                               ->where("school_branch_id", $schoolBranchId)
                               ->where("year", $year)
                               ->where("month", $month)
                               ->where("election_id", $voteDetails->election_id)
                               ->where("stat_type_id", $kpi->id)
                               ->first();
        if($electionStat){
             $electionStat->integer_value++;
             $electionStat->save();
        }
        DB::table('election_vote_stats')->insert([
             'id' => Str::uuid(),
             'decimal_value' => null,
             'integer_value' => 1,
             'json_value' => null,
             'stat_type_id' => $kpi->id,
             'election_id' => $voteDetails->election_id,
             'school_branch_id' => $schoolBranchId,
             'month' => $month,
             'year' => $year,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }

    private function totalElectionVotesByDepartment($year, $month, $schoolBranchId, $voteDetails, $kpi){
        $electionStat = DB::table('election_vote_stats')
                               ->where("school_branch_id", $schoolBranchId)
                               ->where("year", $year)
                               ->where("month", $month)
                               ->where("department_id", $voteDetails->student->department_id)
                               ->where("election_id", $voteDetails->election_id)
                               ->where("stat_type_id", $kpi->id)
                               ->first();
        if($electionStat){
             $electionStat->integer_value++;
             $electionStat->save();
        }
        DB::table('election_vote_stats')->insert([
             'id' => Str::uuid(),
             'decimal_value' => null,
             'integer_value' => 1,
             'json_value' => null,
             'stat_type_id' => $kpi->id,
             'department_id' => $voteDetails->student->department_id,
             'election_id' => $voteDetails->election_id,
             'school_branch_id' => $schoolBranchId,
             'month' => $month,
             'year' => $year,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }

    private function totalElectionVotesBySpecialty($year, $month, $schoolBranchId, $voteDetails, $kpi){
        $electionStat = DB::table('election_vote_stats')
                               ->where("school_branch_id", $schoolBranchId)
                               ->where("year", $year)
                               ->where("month", $month)
                               ->where("specialty_id", $voteDetails->student->specialty_id)
                               ->where("election_id", $voteDetails->election_id)
                               ->where("stat_type_id", $kpi->id)
                               ->first();
        if($electionStat){
             $electionStat->integer_value++;
             $electionStat->save();
        }
        DB::table('election_vote_stats')->insert([
             'id' => Str::uuid(),
             'decimal_value' => null,
             'integer_value' => 1,
             'json_value' => null,
             'stat_type_id' => $kpi->id,
             'department_id' => $voteDetails->student->department_id,
             'election_id' => $voteDetails->election_id,
             'school_branch_id' => $schoolBranchId,
             'month' => $month,
             'year' => $year,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }

}
