<?php

namespace App\Jobs\DataCleanupJobs;

use App\Models\SchoolEvent;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
class UpdateSchoolEventStatusJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    protected string $schoolEventId;
    protected string $schoolBranchId;

    protected $authenticatedUser;

    public function __construct(string $schoolEventId, string $schoolBranchId, $authenticatedUser)
    {
        $this->schoolEventId = $schoolEventId;
        $this->schoolBranchId = $schoolBranchId;
        $this->authenticatedUser = $authenticatedUser;
    }

    public function handle(): void
    {
        $schoolEvent = SchoolEvent::query()
            ->where('school_branch_id', $this->schoolBranchId)
            ->findOrFail($this->schoolEventId);

        $currentDate = Carbon::now();
        $startDate = Carbon::parse($schoolEvent->start_date);
        $endDate = Carbon::parse($schoolEvent->end_date);

        $newStatus = $currentDate->between($startDate, $endDate) ? 'active' : ($currentDate->greaterThan($endDate) ? 'expired' : $schoolEvent->status);

        if ($schoolEvent->status !== $newStatus) {
            $schoolEvent->update(['status' => $newStatus]);
        }

        if($newStatus == 'expired'){
           $schoolEvent->update(['visibility_status' => "hidden"]);
        }
    }
}
