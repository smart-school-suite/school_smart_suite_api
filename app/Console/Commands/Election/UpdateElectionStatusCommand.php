<?php

namespace App\Console\Commands\Election;

use App\Services\Election\ElectionOfficeHolderService;
use App\Services\Election\ElectionService;
use Illuminate\Console\Command;

class UpdateElectionStatusCommand extends Command
{
    protected $signature = 'elections:update-statuses';

    protected $description = 'Advance election lifecycles and office-holder tenures based on their configured dates';

    public function handle(ElectionService $electionService, ElectionOfficeHolderService $officeHolderService): int
    {
        $elections = $electionService->syncStatuses();
        $holders = $officeHolderService->syncStatuses();

        $this->info("Updated {$elections} election(s) and {$holders} office holder(s).");

        return self::SUCCESS;
    }
}
