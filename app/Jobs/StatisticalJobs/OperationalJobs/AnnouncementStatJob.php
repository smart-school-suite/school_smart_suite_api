<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\Announcement; // Ensure this model is correctly configured
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnnouncementStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the school branch.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * The ID of the announcement.
     * @var string
     */
    protected readonly string $announcementId;

    /**
     * Create a new job instance.
     *
     * @param string $schoolBranchId The ID of the school branch.
     * @param string $announcementId The ID of the announcement.
     */
    public function __construct(string $schoolBranchId, string $announcementId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->announcementId = $announcementId;
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
            "total_announcement_count",
            "total_announcement_count_by_type"
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $announcement = Announcement::where("school_branch_id", $this->schoolBranchId)
            ->find($this->announcementId);

        if (!$announcement) {
            Log::warning("Announcement with ID '{$this->announcementId}' not found in school branch '{$this->schoolBranchId}'. Skipping announcement stats job.");
            return;
        }

        $totalAnnouncementKpi = $kpis->get('total_announcement_count');
        if ($totalAnnouncementKpi) {
            $this->upsertAnnouncementStat(
                $year,
                $month,
                $this->schoolBranchId,
                $totalAnnouncementKpi,
                null
            );
        } else {
            Log::warning("StatType 'total_announcement_count' not found. Skipping general announcement count.");
        }



        $announcementTypeKpi = $kpis->get('total_announcement_count_by_type');
        if ($announcementTypeKpi) {
            if ($announcement->label_id) {
                $this->upsertAnnouncementStat(
                    $year,
                    $month,
                    $this->schoolBranchId,
                    $announcementTypeKpi,
                    null
                );
            } else {
                Log::info("Announcement '{$this->announcementId}' has no label ID. Skipping announcement count by type.");
            }
        } else {
            Log::warning("StatType 'total_announcement_count_by_type' not found. Skipping announcement count by type.");
        }
    }

    /**
     * Inserts or updates an announcement statistic record, incrementing its integer_value.
     *
     * @param int $year The year for the statistic.
     * @param int $month The month for the statistic.
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI. (Non-nullable here as checked in handle)
     * @param string|null $announcementId The announcement ID, if the stat is announcement-specific.
     * @param string|null $labelId The label ID, if the stat is label-specific.
     * @return void
     */
    private function upsertAnnouncementStat(
        int $year,
        int $month,
        string $schoolBranchId,
        StatTypes $kpi,
        ?string $labelId = null
    ): void {

        $matchCriteria = [
            'year' => $year,
            'month' => $month,
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'label_id' => $labelId,
        ];


        $existingStat = DB::table('announcement_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('announcement_stats')
                ->where($matchCriteria)
                ->increment('integer_value', 1, ['updated_at' => now()]);
            Log::info("Incremented existing announcement stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, year: {$year}, month: {$month},  label_id: {$labelId}.");
        } else {

            DB::table('announcement_stats')->insert([
                'id' => Str::uuid(),
                'year' => $year,
                'month' => $month,
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'label_id' => $labelId,
                'integer_value' => 1,
                'decimal_value' => null,
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new announcement stat for KPI '{$kpi->program_name}' for school: {$schoolBranchId}, year: {$year}, month: {$month},  label_id: {$labelId}.");
        }
    }
}
