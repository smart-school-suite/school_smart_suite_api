<?php

namespace App\Console\Commands\SystemJob;

use App\Services\Import\StalledImportJobService;
use Illuminate\Console\Command;

class FailStalledImportJobsCommand extends Command
{
    protected $signature = 'system-jobs:fail-stalled-imports';

    protected $description = 'Mark import jobs that stopped reporting (worker crashed or restarted) as failed, so they do not show as running forever';

    public function handle(StalledImportJobService $service): int
    {
        $count = $service->failStalled();

        $this->info("Marked {$count} stalled import job(s) as failed.");

        return self::SUCCESS;
    }
}
