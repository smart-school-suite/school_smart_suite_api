<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdminExamResultsReleased;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ExamResultsAvailable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;
class SendExamResultsReleasedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $examCandidates;
    protected $exam;
    public function __construct($examCandidates, $exam)
    {
        $this->examCandidates = $examCandidates;
        $this->exam = $exam;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolAdmins = $this->getAuthorizedAdmins($this->exam->school_branch_id);
        Notification::send($schoolAdmins, new AdminExamResultsReleased($this->exam));
        Notification::send($this->examCandidates->pluck('student'),
         new ExamResultsAvailable( $this->exam));
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
