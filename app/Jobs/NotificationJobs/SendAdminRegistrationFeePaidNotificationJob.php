<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdminRegistrationFeePaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Schooladmin;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\Log;

class SendAdminRegistrationFeePaidNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    /**
     * Create a new job instance.
     */
    protected $registrationFeeData;
    protected $schoolBranchId;
    public function __construct($registrationFeeData, $schoolBranchId)
    {
        $this->registrationFeeData = $registrationFeeData;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $authorizedAdmins = $this->getAuthorizedAdmins($this->schoolBranchId);
        Log::info("sending Notifications to", $authorizedAdmins->toArray());
        foreach($authorizedAdmins as $authorizedAdmin){
             foreach($this->registrationFeeData as $feeData){
                 $authorizedAdmin->notify(new AdminRegistrationFeePaid(
                    $feeData['student']->name,
                    $feeData['amount'],
                    now()->format('F j, Y, g:i a')
                 ));
             }
        }

    }

     private function getAuthorizedAdmins($schoolBranchId)
    {
        $electionPermissionNames = PermissionCategory::with('permission')
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
