<?php

namespace App\Jobs\NotificationJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;

class SendAdminAdditionalFeeReminderNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected string $additionalFeeId;
    public $tries = 3;
    public function __construct(string $additionalFeeId)
    {
        $this->additionalFeeId = $additionalFeeId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {

    }

    private function getAdditionalFeeManager(string $schoolBranchId): \Illuminate\Database\Eloquent\Collection
    {
        $electionPermissionNames = PermissionCategory::with('permission')
            ->where('title', 'Additional Fee Manager')
            ->first()
            ?->permission
            ->pluck('name')
            ->toArray();

        if (empty($electionPermissionNames)) {
            return collect();
        }

        return Schooladmin::where('school_branch_id', $schoolBranchId)
            ->get()
            ->filter(fn($admin) => $admin->hasAnyPermission($electionPermissionNames));
    }
}
