<?php

namespace App\Jobs\DataCreationJob;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateSchoolBranchAppSettingJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    protected string $schoolBranchId;
    public function __construct(string $schoolBranchId)
    {
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
