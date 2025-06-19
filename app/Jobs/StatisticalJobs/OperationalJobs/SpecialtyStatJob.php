<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\StatTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade for error reporting
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
     * The ID of the specialty for which statistics are being calculated.
     * @var string
     */
    protected readonly string $specialtyId;

    /**
     * The ID of the school branch where the specialty belongs.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $specialtyId The ID of the specialty.
     * @param string $schoolBranchId The ID of the school branch.
     */
    public function __construct(string $specialtyId, string $schoolBranchId)
    {
        $this->specialtyId = $specialtyId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $kpiNames = [
            'total_number_of_specialties'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');


        $totalSpecialtiesKpi = $kpis->get('total_number_of_specialties');
        if (!$totalSpecialtiesKpi) {
            Log::warning("StatType for 'total_number_of_specialties' not found. Skipping specialty total count statistic for school: {$this->schoolBranchId}.");
            return;
        }

        $this->upsertSpecialtyCount(
            $this->schoolBranchId,
            $totalSpecialtiesKpi
        );
    }

    /**
     * Inserts or updates the total specialty count statistic.
     *
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI. (No longer nullable here as checked in handle)
     * @return void
     */
    private function upsertSpecialtyCount(string $schoolBranchId, StatTypes $kpi): void
    {

        $matchCriteria = [
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
        ];

        $existingStat = DB::table('specialty_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('specialty_stats')
                ->where($matchCriteria)
                ->increment('integer_value', 1, ['updated_at' => now()]);
            Log::info("Incremented existing stat for 'total_number_of_specialties' for school: {$schoolBranchId}.");
        } else {
            DB::table('specialty_stats')->insert([
                'id' => Str::uuid(),
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'integer_value' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new stat for 'total_number_of_specialties' for school: {$schoolBranchId}.");
        }
    }
}
