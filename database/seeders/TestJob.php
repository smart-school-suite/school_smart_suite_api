<?php

namespace Database\Seeders;

use App\Jobs\StatisticalJobs\OperationalJobs\ElectionWinnerStatJob;
use App\Models\RegistrationFee;
use App\Notifications\ExamResultsAvailable;
use App\Notifications\TestNotification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Jobs\DataCreationJob\CreateResitExamJob;
use App\Models\Exams;
use App\Models\Schooladmin;
use App\Models\Student;

class TestJob extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

    }
}
