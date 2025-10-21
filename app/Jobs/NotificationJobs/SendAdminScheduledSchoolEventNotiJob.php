<?php

namespace App\Jobs\NotificationJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SchoolEvent;
use App\Notifications\AdminEventScheduledNotification;
class SendAdminScheduledSchoolEventNotiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    protected string $schoolEventId;
    protected  $author;
    protected string $schoolBranchId;

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
        $schoolEvent = SchoolEvent::query()
            ->where('school_branch_id', $this->schoolBranchId)
            ->findOrFail($this->schoolEventId);

        $this->author['authUser']->notify(new AdminEventScheduledNotification($schoolEvent));
    }
}
