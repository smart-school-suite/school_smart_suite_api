<?php

namespace App\Jobs\NotificationJobs;

use App\Models\ElectionApplication;
use App\Notifications\AdminApplicationApproved;
use App\Notifications\CandidacyApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendCandidacyApprovedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    protected array $applicationData;

    public function __construct(array $applicationData)
    {
        $this->applicationData = $applicationData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $applicationData = $this->applicationData;
        foreach($applicationData as $application){
            $application['student']->notify(new CandidacyApproved(
                $application['electionRole']->name,
                $application['election']->electionType->election_title
            ));
        }
    }


}
