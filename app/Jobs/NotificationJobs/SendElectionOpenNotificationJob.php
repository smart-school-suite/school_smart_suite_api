<?php

namespace App\Jobs\NotificationJobs;

use App\Models\ElectionParticipants;
use App\Models\Elections;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;
use App\Models\Student;
use App\Notifications\ElectionApplicationOpen;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendElectionOpenNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $election = Elections::with(['electionType'])->find($this->electionId);

        if (!$election) {
            return;
        }

        $usersToNotify = collect();

        $students = $this->getEligibleStudents($election->id, $this->schoolBranchId);
        $usersToNotify = $usersToNotify->merge($students);

        $schoolAdmins = $this->getElectionManagers($this->schoolBranchId);
        $usersToNotify = $usersToNotify->merge($schoolAdmins);

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new ElectionApplicationOpen($election));
        }
    }

    /**
     * Get students eligible to participate in the election for the given school branch.
     *
     * @param string $electionId
     * @param string $schoolBranchId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getEligibleStudents(string $electionId, string $schoolBranchId): \Illuminate\Database\Eloquent\Collection
    {
        $allowedSpecialtyIds = ElectionParticipants::where('school_branch_id', $schoolBranchId)
            ->where('election_id', $electionId)
            ->pluck('specialty_id');

        return Student::where('school_branch_id', $schoolBranchId)
            ->whereIn('specialty_id', $allowedSpecialtyIds)
            ->get();
    }

    /**
     * Get school admins with 'Election Manager' permissions for the given school branch.
     *
     * @param string $schoolBranchId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getElectionManagers(string $schoolBranchId): \Illuminate\Database\Eloquent\Collection
    {
        $electionPermissionNames = PermissionCategory::with('permissions')
            ->where('name', 'Election Manager')
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
