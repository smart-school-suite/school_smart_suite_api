<?php

namespace Database\Seeders;

use App\Jobs\DataCleanupJobs\UpdateElectionResultStatus;
use Illuminate\Database\Seeder;
use App\Models\CurrentElectionWinners;

use Illuminate\Support\Facades\DB;

class test extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $electionId = "55681183-47a5-431d-9fe4-82d89b29bf88";
        $schoolBranchId = "63b4ddd0-bf0d-46b5-b333-0c61a57b8b3c";
        UpdateElectionResultStatus::dispatch($electionId, $schoolBranchId);
    }
}
