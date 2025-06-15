<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\Elections;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ElectionStatJob implements ShouldQueue
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
            'total_election_count',
            'total_election_type_count_by_election'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $electionDetails = Elections::findOrFail($electionId);
        $this->totalElectionCount(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get("total_election_count")
        );

        $this->totalElectionCountByElection(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_election_type_count_by_election'),
            $electionDetails
        );
    }

    private function totalElectionCount($year, $month, $schoolBranchId, $kpi){
        $electionStat = DB::table('election_stat')->where("year", $year)
                                    ->where("month", $month)
                                    ->where("stat_type_id", $kpi->id)
                                    ->where("school_branch_id", $schoolBranchId)
                                    ->first();
        if($electionStat){
            $electionStat->interger_value++;
            $electionStat->save();
        }

        DB::table('election_stat')->insert([
             'id' => Str::uuid(),
             'stat_type_id' => $kpi->id,
             'school_branch_id' => $schoolBranchId,
             'interger_value' => 1,
             'decimal_value' => null,
             'json_value' => null,
             'year' => $year,
             'month' => $month,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }
    private function totalElectionCountByElection($year, $month, $schoolBranchId, $kpi, $electionDetails){
        $stat = DB::table("election_stat")->where("year", $year)
                                          ->where("month", $month)
                                          ->where("stat_type_id", $kpi->id)
                                          ->where("election_type_id", $electionDetails->election_type_id)
                                          ->where("school_branch_id", $schoolBranchId)
                                          ->first();
        if($stat){
            $stat->interger_value++;
            $stat->save();
        }

        DB::table('election_stat')->insert([
             'id' => Str::uuid(),
             'stat_type_id' => $kpi->id,
             'school_branch_id' => $schoolBranchId,
             'election_type_id' => $electionDetails->election_type_id,
             'interger_value' => 1,
             'decimal_value' => null,
             'json_value' => null,
             'year' => $year,
             'month' => $month,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }
}
