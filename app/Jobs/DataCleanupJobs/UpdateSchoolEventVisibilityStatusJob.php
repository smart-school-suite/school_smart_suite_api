<?php

namespace App\Jobs\DataCleanupJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\SchoolEvent;
class UpdateSchoolEventVisibilityStatusJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    protected string $schoolBranchId;
    protected string $schoolEventId;
    public function __construct(string $schoolBranchId, string $schoolEventId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->schoolEventId = $schoolEventId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolEvent = SchoolEvent::where("school_branch_id", $this->schoolBranchId)
                                    ->find($this->schoolEventId);
        $schoolEvent->visibility_status = 'visible';
        $schoolEvent->save();
    }
}
