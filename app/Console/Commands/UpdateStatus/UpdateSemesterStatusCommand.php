<?php

namespace App\Console\Commands\UpdateStatus;

use Illuminate\Console\Command;
use App\Jobs\UpdateStatus\UpdateSemesterStatusJob;

class UpdateSemesterStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'semesters:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch job to update all semester statuses based on current date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching semester status update job...');

        UpdateSemesterStatusJob::dispatch();

        $this->info('Job dispatched successfully.');

        return self::SUCCESS;
    }
}
