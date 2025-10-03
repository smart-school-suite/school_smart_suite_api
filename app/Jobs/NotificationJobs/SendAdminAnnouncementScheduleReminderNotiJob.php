<?php

namespace App\Jobs\NotificationJobs;

use App\Models\Announcement;
use App\Notifications\ScheduledAnnouncementReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAdminAnnouncementScheduleReminderNotiJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    protected string $announcementId;
    protected  $author;
    protected string $schoolBranchId;
     public function __construct(string $announcementId, $author, string $schoolBranchId)
    {
       $this->announcementId = $announcementId;
       $this->author = $author;
       $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       $announcement = Announcement::where("school_branch_id", $this->schoolBranchId)
                       ->find($this->announcementId);
       $this->author['authUser']->notify(new ScheduledAnnouncementReminderNotification($announcement));

    }
}
