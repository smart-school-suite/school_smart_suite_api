<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdminExamTimetableAvialable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Schooladmin;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\Notification;

class SendAdminExamTimetableAvailableNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    /**
     * Create a new job instance.
     */
    protected string $schoolBranchId;
    protected array $examData;
    public function __construct(string $schoolBranchId, array $examData)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->examData = $examData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $schoolAdmins = $this->getAuthorizedAdmins($this->schoolBranchId);
        Notification::send($schoolAdmins, new AdminExamTimetableAvialable($this->examData));
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
