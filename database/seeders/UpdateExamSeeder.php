<?php

namespace Database\Seeders;

use App\Models\Exams;
use App\Models\SchoolGradesConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UpdateExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Retrieve exams with null grades_category_id
        $exams = Exams::where("grades_category_id", null)->get();

        // Retrieve all school grades configurations that are configured
        $schoolGradesConfig = SchoolGradesConfig::where("isgrades_configured", 1)->get();

        Log::info("school grades config", $schoolGradesConfig->toArray());

        // Shuffle the school grades config to randomize
        $schoolGradesConfigShuffled = $schoolGradesConfig->shuffle();

        foreach ($exams as $exam) {
            // Select a random configuration from the shuffled collection
            $matchingConfig = $schoolGradesConfigShuffled->random();

            // Assign the random configuration values to the exam
            $exam->grades_category_id = $matchingConfig->grades_category_id;
            $exam->weighted_mark = $matchingConfig->max_score;

            // Save the exam
            $exam->save();
        }

        $this->command->info("Exam table updated successfully");
    }
}
