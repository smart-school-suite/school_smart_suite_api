<?php

namespace App\Jobs\NotificationJobs;

use App\Models\SchoolEvent\SchoolEvent;
use App\Notifications\ScheduledSchoolEventReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAdminEventScheduleReminderNotiJob implements ShouldQueue
{
     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    protected string $schoolEventId;
    protected  $author;
    protected string $schoolBranchId;
    /**
     * Create a new job instance.
     */
    public function __construct(string $schoolEventId, $author, string $schoolBranchId)
    {
        $this->schoolEventId = $schoolEventId;
       $this->author = $author;
       $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolEvent = SchoolEvent::where("school_branch_id", $this->schoolBranchId)
                                ->where("status", "scheduled")
                                ->find($this->schoolEventId);

        if (!$schoolEvent) {
            return;
        }

        $this->author['authUser']->notify(new ScheduledSchoolEventReminderNotification($schoolEvent));
    }
}
