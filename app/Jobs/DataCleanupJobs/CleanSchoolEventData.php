<?php

namespace App\Jobs\DataCleanupJobs;

use App\Models\EventLikeStatus;
use App\Models\SchoolEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CleanSchoolEventData implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    protected string $schoolEventId;
    protected string $schoolBranchId;
    public function __construct(string $schoolEventId, string $schoolBranchId)
    {
        $this->schoolEventId = $schoolEventId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolEventId = $this->schoolEventId;
        $schoolBranchId = $this->schoolBranchId;
        $schoolEvent = SchoolEvent::where("school_branch_id", $schoolBranchId)
                        ->find($schoolEventId);
        $eventLikeStatuses = EventLikeStatus::where("school_branch_id", $schoolBranchId)
                                           ->where("event_id", $schoolEvent->id)
                                           ->get();
        foreach($eventLikeStatuses as $eventLikeStatus){
             $eventLikeStatus->delete();
        }
    }
}
