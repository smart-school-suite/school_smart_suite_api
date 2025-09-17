<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdminResitDetectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Notification;

class SendAdminResitExamCreatedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $tries = 3;
    protected $schoolBranchId;
    protected $resitDetails;
    protected $examDetails;
    public function __construct($schoolBranchId, $resitDetails, $examDetails)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->resitDetails = $resitDetails;
        $this->examDetails = $examDetails;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolAdmins = $this->getAuthorizedAdmins($this->schoolBranchId);
        Notification::send($schoolAdmins,
        new AdminResitDetectedNotification($this->examDetails, $this->resitDetails));
    }

     private function getAuthorizedAdmins($schoolBranchId)
    {
        $electionPermissionNames = PermissionCategory::with('permission')
            ->where('title', 'Exam Manager')
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
