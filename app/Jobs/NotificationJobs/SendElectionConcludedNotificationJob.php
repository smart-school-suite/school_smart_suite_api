<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\ElectionConcluded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Election\ElectionParticipant;
use App\Models\Election\Election;
use App\Models\Schooladmin;
use App\Models\Student;
use App\Models\PermissionCategory;
use Illuminate\Support\Facades\Notification;

class SendElectionConcludedNotificationJob implements ShouldQueue
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
        $election = Election::with(['electionType'])->find($electionId);
        $students = $this->getEligibleStudents($electionId, $schoolBranchId);
        Notification::send($students, new ElectionConcluded($election->electionType->election_title));
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
            ->with('participantable')
            ->get()
            ->pluck('participantable')
            ->filter();
    }


}
