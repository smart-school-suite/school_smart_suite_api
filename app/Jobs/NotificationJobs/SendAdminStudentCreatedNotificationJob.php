<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\StudentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Schooladmin;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendAdminStudentCreatedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    /**
     * Create a new job instance.
     */
    protected $specialty;
    protected $studentName;
    protected $level;
    protected $schoolBranchId;
    public function __construct($specialty, $studentName, $level, $schoolBranchId)
    {
        $this->specialty = $specialty;
        $this->studentName = $studentName;
        $this->level = $level;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolAdmins  = $this->getAuthorizedAdmins($this->schoolBranchId);

        Notification::send($schoolAdmins, new StudentCreated(
            $this->studentName,
                         $this->level,
                         $this->specialty
        ));
    }

     private function getAuthorizedAdmins($schoolBranchId)
    {
        $studentManagerPermissions = PermissionCategory::with('permission')
            ->where('title', 'Student Manager')
            ->first()
            ?->permission
            ->pluck('name')
            ->toArray();

        if (empty($studentManagerPermissions)) {
            return collect();
        }


        return Schooladmin::where('school_branch_id', $schoolBranchId)
            ->get()
            ->filter(fn($admin) => $admin->hasAnyPermission($studentManagerPermissions));
    }
}
