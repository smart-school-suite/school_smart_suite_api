<?php

namespace App\Jobs\DataCleanupJobs;

use App\Models\Announcement;
use App\Models\AnnouncementEngagementStat;
use App\Models\SchoolAdminAnnouncement;
use App\Models\StudentAnnouncement;
use App\Models\TeacherAnnouncement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateAnnouncementStatusJob implements ShouldQueue
{
    use Queueable;

    protected string $announcementId;
    protected string $schoolBranchId;

    public function __construct($announcementId, $schoolBranchId)
    {
        $this->announcementId = $announcementId;
        $this->schoolBranchId = $schoolBranchId;
    }

    public function handle(): void
    {
        $announcement = Announcement::where('school_branch_id', $this->schoolBranchId)
            ->find($this->announcementId);

        if ($announcement) {
            $announcement->update(['status' => 'expired']);
            $this->deleteUserAnnouncements($this->announcementId, $this->schoolBranchId);
            $this->deleteAnnouncementEngagementStats($this->announcementId, $this->schoolBranchId);
        }
    }

    protected function deleteUserAnnouncements($announcementId, $schoolBranchId): void
    {
        StudentAnnouncement::where('school_branch_id', $schoolBranchId)
            ->where('announcement_id', $announcementId)
            ->delete();

        TeacherAnnouncement::where('school_branch_id', $schoolBranchId)
            ->where('announcement_id', $announcementId)
            ->delete();

        SchoolAdminAnnouncement::where('school_branch_id', $schoolBranchId)
            ->where('announcement_id', $announcementId)
            ->delete();
    }

    protected function deleteAnnouncementEngagementStats($announcementId, $schoolBranchId): void
    {
        AnnouncementEngagementStat::where('school_branch_id', $schoolBranchId)
            ->where('announcement_id', $announcementId)
            ->delete();
    }
}
