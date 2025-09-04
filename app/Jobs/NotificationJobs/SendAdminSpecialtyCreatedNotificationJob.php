<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdminSpecialtyCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Notification;
class SendAdminSpecialtyCreatedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $schoolBranchId;
    protected $specialtyDetails;

    public function __construct($schoolBranchId, $specialtyDetails)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->specialtyDetails = $specialtyDetails;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       $schoolAdmins = $this->getAuthorizedAdmins($this->schoolBranchId);
       Notification::send($schoolAdmins,
       new AdminSpecialtyCreated($this->schoolBranchId, $this->specialtyDetails));
    }

     private function getAuthorizedAdmins($schoolBranchId)
    {
        $electionPermissionNames = PermissionCategory::with('permission')
            ->where('title', 'Department Manager')
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
