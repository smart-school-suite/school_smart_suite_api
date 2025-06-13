<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Jobs\StatisticalJobs\AcademicJobs\StudentCaStatsJob;
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
        $exam = Exams::findOrFail("b9be6a18-96b2-4416-9983-75c6ca5b7462");
        $student = Student::findOrFail("08542652-cd3c-45d3-a1e6-cceddadbbdb0");

        StudentCaStatsJob::dispatch($exam, $student);
    }
}
