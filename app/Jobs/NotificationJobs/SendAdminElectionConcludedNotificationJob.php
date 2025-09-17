<?php

namespace App\Jobs\NotificationJobs;

use App\Models\Elections;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;
use App\Notifications\AdminElectionConcluded;
use Illuminate\Support\Facades\Notification;

class SendAdminElectionConcludedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    /**
     * The ID of the election.
     *
     * @var string
     */
    protected string $electionId;

    /**
     * The ID of the school branch.
     *
     * @var string
     */
    protected string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $electionId
     * @param string $schoolBranchId
     */
    public function __construct(string $electionId, string $schoolBranchId)
    {
        $this->electionId = $electionId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $electionId = $this->electionId;
        $schoolBranchId = $this->schoolBranchId;
        $election = Elections::with('electionType')->find($electionId);
        $schoolAdmins = $this->getElectionManagers($schoolBranchId);
        Notification::send($schoolAdmins, new AdminElectionConcluded($election->electionType->election_title));
    }

    /**
     * Get school admins with 'Election Manager' permissions for the given school branch.
     *
     * @param string $schoolBranchId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getElectionManagers(string $schoolBranchId): \Illuminate\Database\Eloquent\Collection
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
            ->filter(fn ($admin) => $admin->hasAnyPermission($electionPermissionNames));
    }
}
