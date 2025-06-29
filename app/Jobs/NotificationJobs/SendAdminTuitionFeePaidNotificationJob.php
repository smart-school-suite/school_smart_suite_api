<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdminTuitionFeePaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Schooladmin;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\Notification;

class SendAdminTuitionFeePaidNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $schoolBranchId;
    protected $student;
    protected $paymentDetails;
    public function __construct(string $schoolBranchId, $student, $paymentDetails)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->student = $student;
        $this->paymentDetails = $paymentDetails;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $authorizedAdmins = $this->getAuthorizedAdmins($this->schoolBranchId);
        Notification::send(
            $authorizedAdmins,
            new AdminTuitionFeePaid(
                $this->student->name,
                $this->paymentDetails['amountPaid'],
                $this->paymentDetails['balanceLeft'],
                $this->paymentDetails['paymentDate'],

            )
        );
    }

    private function getAuthorizedAdmins($schoolBranchId)
    {
        $electionPermissionNames = PermissionCategory::with('permissions')
            ->where('name', 'Tuition Fee Manager')
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
