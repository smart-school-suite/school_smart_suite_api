<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdminFeeScheduleAvailable;
use App\Notifications\FeeScheduleAvailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Schooladmin;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\Notification;

class SendAdminFeeScheduleNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $schoolBranchId;
    protected $scheduleData;
    public function __construct($schoolBranchId, $scheduleData)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->scheduleData = $scheduleData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       $authAdmins = $this->getAuthorizedAdmins($this->schoolBranchId);
       Notification::send($authAdmins, new AdminFeeScheduleAvailable(
        $this->scheduleData['schoolYear'],
        $this->scheduleData['semester'],
        $this->scheduleData['specialty'],
        $this->scheduleData['level']
       ));
    }

    private function getAuthorizedAdmins($schoolBranchId)
    {
        $electionPermissionNames = PermissionCategory::with('permissions')
            ->where('title', 'Tuition Fee Manager')
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
