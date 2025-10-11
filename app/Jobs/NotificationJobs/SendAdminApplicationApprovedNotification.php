<?php

namespace App\Jobs\NotificationJobs;

use App\Models\ElectionApplication;
use App\Notifications\AdminApplicationApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Schooladmin;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\Notification;

class SendAdminApplicationApprovedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected array $applicationData;
    protected string $schoolBranchId;

    public function __construct(array $applicationData, string $schoolBranchId)
    {
        $this->applicationData = $applicationData;
        $this->schoolBranchId = $schoolBranchId;
    }

    public function handle(): void
    {
        $schoolAdminsToNotify = $this->getAuthorizedAdmins($this->schoolBranchId);

        if ($schoolAdminsToNotify->isEmpty()) {
            return;
        }

        foreach ($this->applicationData as $application) {
            $applicationId = $application['application_id'] ?? null;
            if (!$applicationId) {
                continue;
            }

            $applicationModel = ElectionApplication::where("school_branch_id", $this->schoolBranchId)
                ->with(['election.electionType', 'electionRole', 'student'])
                ->find($applicationId);

            if (!$applicationModel || !$applicationModel->student) {
                continue;
            }

            Notification::send(
                $schoolAdminsToNotify,
                new AdminApplicationApproved(
                    $applicationModel->student->name,
                    $applicationModel->electionRole->name,
                    $applicationModel->election->electionType->election_title
                )
            );
        }
    }

    private function getAuthorizedAdmins(string $schoolBranchId)
    {
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
            ->filter(fn($admin) => $admin->hasAnyPermission($electionPermissionNames));
    }
}
