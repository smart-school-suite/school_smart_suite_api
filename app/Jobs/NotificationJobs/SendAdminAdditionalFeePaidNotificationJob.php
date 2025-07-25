<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdminAdditionalFeePaidNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Notification;

class SendAdminAdditionalFeePaidNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $schoolBranchId;
    protected $additionalFeeData;
    public function __construct(string $schoolBranchId, array $additionalFeeData)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->additionalFeeData = $additionalFeeData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $admins = $this->getAuthorizedAdmins($this->schoolBranchId);
        foreach($this->additionalFeeData as $fee){
           Notification::send($admins, new AdminAdditionalFeePaidNotification(
                 $fee['student']->name,
                 $fee['amount'],
                 $fee['reason'],
                 'XAF'
           ));
        }
    }

     private function getAuthorizedAdmins($schoolBranchId){
                $electionPermissionNames = PermissionCategory::with('permissions')
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
            ->filter(fn ($admin) => $admin->hasAnyPermission($electionPermissionNames));
    }
}
