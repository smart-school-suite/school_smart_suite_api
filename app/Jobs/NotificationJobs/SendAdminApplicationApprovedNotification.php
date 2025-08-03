<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdminApplicationApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Schooladmin;
use App\Models\PermissionCategory;


class SendAdminApplicationApprovedNotification implements ShouldQueue
{
     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $applicationData;
    protected $schoolBranchId;
    public function __construct(array $applicationData, string $schoolBranchId)
    {
        $this->applicationData = $applicationData;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $schoolAdminsToNotify = $this->getAuthorizedAdmins($this->schoolBranchId);
        if($schoolAdminsToNotify->isNotEmpty()){
            foreach($this->applicationData as $application){
                $application['student']->notify(new AdminApplicationApproved(
                    $application['student']->name,
                     $application['electionRole']->name,
                      $application['election']->electionType->election_title
                ));
            }
        }
    }

    private function getAuthorizedAdmins($schoolBranchId){
                $electionPermissionNames = PermissionCategory::with('permission')
            ->where('title', 'Election Manager')
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
