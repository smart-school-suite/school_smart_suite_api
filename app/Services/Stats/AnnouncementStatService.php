<?php

namespace App\Services\Stats;

use App\Models\Announcement;
use App\Models\StatTypes;
use App\Models\AnnouncementLabel;
use Illuminate\Support\Facades\DB;

class AnnouncementStatService
{
    public function getAnnouncementStats($currentSchool, $year = null)
    {
        $year = $year ?? now()->year;

        $kpis = StatTypes::whereIn('program_name', [
            "total_announcement_count",
            "total_announcement_count_by_type"
        ])->get()->keyBy('program_name');

        $announceCountKpi = $kpis->get('total_announcement_count');
        $announceCountTypeKpi = $kpis->get('total_announcement_count_by_type');

        if (!$announceCountKpi || !$announceCountTypeKpi) {
            return [];
        }

        $announceCountKpiId = $announceCountKpi->id;
        $announceCountTypeKpiId = $announceCountTypeKpi->id;

        $labels = AnnouncementLabel::all()->keyBy('name');

        $announcementData = Announcement::where("school_branch_id", $currentSchool->id)
            ->with(['announcementLabel', 'announcementCategory'])
            ->whereYear('created_at', $year)
            ->get();

        $announcementStatData = DB::table('announcement_stats')
            ->where("school_branch_id", $currentSchool->id)
            ->whereIn('stat_type_id', [$announceCountKpiId, $announceCountTypeKpiId])
            ->where("year", $year)
            ->get();

        $stats = [];

        $stats['recent_announcements_by_status'] = $this->formatAnnouncementData($announcementData);
        $stats['announcements_by_type'] = $this->formatAnnouncementByType($announcementData, $labels);
        $stats['announcement_numbers_by_type'] = $this->formatAnnouncementNumbersByType(
            $announcementStatData,
            $announceCountTypeKpiId,
            $labels
        );
        $stats['total_announcement_numbers'] = $this->getAnnouncementNumbers($announcementData);

        return $stats;
    }

    public function formatAnnouncementData($announcementData)
    {
        return [
            'active_announcement' => $announcementData->where("status", "active")->sortByDesc('created_at')->take(5)->values(),
            'scheduled_announcement' => $announcementData->where("status", "scheduled")->sortByDesc('created_at')->take(5)->values(),
            'draft_announcement' => $announcementData->where("status", "draft")->sortByDesc('created_at')->take(5)->values(),
        ];
    }

    public function formatAnnouncementByType($announcementData, $labels)
    {
        $urgentLabelId = $labels->get('urgent') ? $labels->get('urgent')->id : null;

        $urgentLiveAnnouncements = collect([]);

        if ($urgentLabelId) {
            $urgentLiveAnnouncements = $announcementData->where("status", "active")
                ->where("announcement_label_id", $urgentLabelId)
                ->sortByDesc('created_at')
                ->take(5)
                ->values();
        }

        return [
            'urgent_announcements' => $urgentLiveAnnouncements
        ];
    }

    public function formatAnnouncementNumbersByType($announcementStatData, $announceCountTypeKpiId, $labels)
    {
        $announcementStats = [];
        $announcementTypeStat = $announcementStatData->where("stat_type_id", $announceCountTypeKpiId);
        $groupedStats = $announcementTypeStat->groupBy('reference_id');

        foreach ($labels as $label) {
            $labelId = $label->id;
            $labelName = $label->name;
            $totalCount = 0;

            if (isset($groupedStats[$labelId])) {
                $totalCount = $groupedStats[$labelId]->sum('integer_value');
            }

            $announcementStats[] = [
                'label_name' => $labelName,
                'count' => $totalCount,
            ];
        }

        return $announcementStats;
    }

    public function getAnnouncementNumbers($announcementData)
    {
        $announcementNumbers = [];
        $announcementNumbers['total_announcement_count'] = $announcementData->count();
        $announcementNumbers['total_announcement_expired'] = $announcementData->where("status", "expired")->count();
        $announcementNumbers['total_announcement_draft'] = $announcementData->where("status", "draft")->count();
        $announcementNumbers['total_announcement_scheduled'] = $announcementData->where("status", "scheduled")->count();
        $announcementNumbers['total_announcement_active'] = $announcementData->where("status", "active")->count();

        return $announcementNumbers;
    }
}
