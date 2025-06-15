<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\Announcement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use App\Models\StatTypes;
use Illuminate\Support\Facades\DB;

class AnnouncementStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $schoolBranchId;
    protected $announcementId;
    public function __construct(string $schoolBranchId, string $announcementId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->announcementId = $announcementId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $announcementId = $this->announcementId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;

        $kpiNames = [
            "total_announcement_count",
            "total_announcement_count_by_type"
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $announcement = Announcement::where("school_branch_id", $schoolBranchId)
            ->find($announcementId);
        $this->announcementCountStat(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_announcement_count'),
            $announcement
        );
        $this->announcementCountByLabel(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_announcement_count_by_type'),
            $announcement
        );
    }

    private function announcementCountStat($year, $month, $schoolBranchId, $kpi, $announcement)
    {
        $stat = DB::table('announcement_stat')
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->where("year", $year)
            ->where("month", $month)
            ->where("announcement_id", $announcement->id)
            ->first();
        if ($stat) {
            $stat->integer_value++;
            $stat->save();
        }

        DB::table('anouncement_stat')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'year' => $year,
            'month' => $month,
            'announcement_id' => $announcement->id,
            'stat_type_id' => $kpi->id,
            'decimal_value' => null,
            'integer_value' => 1,
            'json_value' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function announcementCountByLabel($year, $month, $schoolBranchId, $kpi, $announcement)
    {
        $stat = DB::table('announcement_stat')
            ->where("school_branch_id", $schoolBranchId)
            ->where("stat_type_id", $kpi->id)
            ->where("label_id", $announcement->label_id)
            ->where("year", $year)
            ->where("month", $month)
            ->first();
        if ($stat) {
            $stat->integer_value++;
            $stat->save();
        }

        DB::table('anouncement_stat')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'year' => $year,
            'month' => $month,
            'label_id' => $announcement->label_id,
            'stat_type_id' => $kpi->id,
            'decimal_value' => null,
            'integer_value' => 1,
            'json_value' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
