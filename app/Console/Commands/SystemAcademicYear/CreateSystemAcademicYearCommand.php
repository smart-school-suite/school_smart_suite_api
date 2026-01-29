<?php

namespace App\Console\Commands\SystemAcademicYear;

use App\Jobs\SystemAcademicYear\CreateSystemAcademicYearJob;
use Illuminate\Console\Command;

class CreateSystemAcademicYearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-system-academic-year-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create system academic year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching create system academic year job...');
        CreateSystemAcademicYearJob::dispatch();
        $this->info('Job dispatched successfully.');
        return self::SUCCESS;
    }
}
