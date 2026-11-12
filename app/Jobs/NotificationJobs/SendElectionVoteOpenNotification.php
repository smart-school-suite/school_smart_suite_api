<?php

namespace App\Jobs\NotificationJobs;

use App\Models\Election\ElectionParticipant;
use App\Models\Election\Election;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;
use App\Models\Student;
use App\Notifications\ElectionVotingOpen;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendElectionVoteOpenNotification implements ShouldQueue
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
        $election = Election::with(['electionType'])->find($this->electionId);

        if (!$election) {
            return;
        }

        $usersToNotify = collect();

        $students = $this->getEligibleStudents($election->id, $this->schoolBranchId);
        $usersToNotify = $usersToNotify->merge($students);

        $schoolAdmins = $this->getElectionManagers($this->schoolBranchId);
        $usersToNotify = $usersToNotify->merge($schoolAdmins);

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new ElectionVotingOpen($election));
        }
    }

    /**
     * Get students eligible to participate in the election for the given school branch.
     *
     * @param string $electionId
     * @param string $schoolBranchId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getEligibleStudents(string $electionId, string $schoolBranchId): \Illuminate\Support\Collection
    {
        // Resolved participants (students, teachers and school admins) of the election.
        return ElectionParticipant::where('school_branch_id', $schoolBranchId)
            ->where('election_id', $electionId)
            ->where('can_vote', true)
            ->with('participantable')
            ->get()
            ->pluck('participantable')
            ->filter();
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
