<?php

namespace App\Jobs\NotificationJobs;

use App\Models\ElectionApplication;
use App\Models\Student;
use App\Notifications\CandidacyApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class SendCandidacyApprovedNotification implements ShouldQueue
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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $applicationData = $this->applicationData;
        $schoolBranchId = $this->schoolBranchId;

        foreach ($applicationData as $application) {
            $student = Student::where("school_branch_id", $schoolBranchId)
                ->find($application['student_id']);

            $application = ElectionApplication::where("school_branch_id", $schoolBranchId)
                ->with(['election.electionType', 'electionRole'])
                ->find($application['application_id']);

            if (!$student || !$application) {
                continue;
            }

            $student->notify(new CandidacyApproved(
                $application->electionRole->name,
                $application->election->electionType->election_title,
            ));
        }
    }
}
