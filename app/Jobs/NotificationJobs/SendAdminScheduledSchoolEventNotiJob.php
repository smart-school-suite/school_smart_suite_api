<?php

namespace App\Jobs\NotificationJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAdminScheduledSchoolEventNotiJob implements ShouldQueue
{
    use Queueable;


    public function __construct()
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
