<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\StatTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SpecialtyStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $specialtyId;
    public $schoolBranchId;
    public function __construct(string $specialtyId, string $schoolBranchId)
    {
        $this->specialtyId = $specialtyId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolBranchId = $this->schoolBranchId;
        $kpiNames = [
            'total_number_of_specialties'
        ];
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $this->totalSpecialtyCount(
            $schoolBranchId,
            $kpis->get('total_number_of_specialties')
        );
    }

    private function totalSpecialtyCount($schoolBranchId, $kpi)
    {
        $specialtyStat = DB::table('specialty_stats')->where("school_branch_id", $schoolBranchId)
            ->where("stat_type_id", $kpi->id)
            ->first();
        if ($specialtyStat) {
            $specialtyStat->interger_value++;
            $specialtyStat->save();
        }

        DB::table('specialty_stats')->insert([
            'id' => Str::uuid(),
            'stat_type_id' => $kpi->id,
            'school_branch_id' => $schoolBranchId,
            'integer_value' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
