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

class DepartmentStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $departmentId;
    public $schoolBranchId;
    public function __construct(string $departmentId, string $schoolBranchId)
    {
       $this->departmentId = $departmentId;
       $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolBranchId = $this->schoolBranchId;
        $kpiNames = [
            'total_number_of_departments'
        ];
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $this->totalDepartmentCount(
            $schoolBranchId,
            $kpis->get('total_number_of_departments')
        );
    }

    private function totalDepartmentCount($schoolBranchId, $kpi){
         $departmentStat = DB::table('department_stat')->where("school_branch_id", $schoolBranchId)
                                                   ->where("stat_type_id", $kpi->id)
                                                   ->first();
        if($departmentStat){
            $departmentStat->interger_value++;
            $departmentStat->save();
        }

        DB::table('department_stat')->insert([
            'id' => Str::uuid(),
            'stat_type_id' => $kpi->id,
            'school_branch_id' => $schoolBranchId,
            'integer_value' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
