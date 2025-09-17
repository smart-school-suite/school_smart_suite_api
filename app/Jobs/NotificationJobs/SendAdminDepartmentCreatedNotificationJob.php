<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdminDepartmentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Notification;

class SendAdminDepartmentCreatedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $tries = 3;
    protected $schoolBranchId;
    protected $departmentDetails;
    public function __construct($schoolBranchId, $departmentDetails)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->departmentDetails = $departmentDetails;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       $schoolAdmins = $this->getAuthorizedAdmins($this->schoolBranchId);
       Notification::send($schoolAdmins,
       new AdminDepartmentCreated($this->schoolBranchId, $this->departmentDetails));
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
