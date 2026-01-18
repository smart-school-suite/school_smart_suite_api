<?php

namespace App\Console\Commands\UpdateStatus;

use Illuminate\Console\Command;
use App\Jobs\UpdateStatus\UpdateExamStatusJob;

class UpdateExamStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exams:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch job to update all exam statuses based on current date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching exam status update job...');

        UpdateExamStatusJob::dispatch();

        $this->info('Job dispatched successfully.');

        return self::SUCCESS;
    }
}
