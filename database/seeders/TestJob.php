<?php

namespace Database\Seeders;

use App\Jobs\StatisticalJobs\OperationalJobs\ElectionWinnerStatJob;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Jobs\StatisticalJobs\AcademicJobs\StudentCaStatsJob;
use App\Jobs\StatisticalJobs\AcademicJobs\CaStatsJob;
use App\Models\ElectionResults;
use App\Models\Exams;
use App\Models\Student;

class TestJob extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $this->command->info("Seeder Has Begun");
        $electionResults = ElectionResults::find("3bc2146086");
        ElectionWinnerStatJob::dispatch($electionResults->election_id, $electionResults->school_branch_id);
    }
}
