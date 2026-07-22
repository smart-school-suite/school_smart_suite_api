<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Qualification;
use App\Models\Teacher;
use App\Models\Educationlevels;

class test extends Seeder
{
    public function run(): void
    {
        // 1. Fetch all records from the database
        $teachers = Teacher::all();
        $levels = Educationlevels::all();
        $qualifications = Qualification::all();

        // Guard clause in case any tables are empty
        if ($teachers->isEmpty() || $levels->isEmpty() || $qualifications->isEmpty()) {
            $this->command->warn('Ensure you have teachers, education levels, and qualifications seeded first!');
            return;
        }

        // Fake specialties array to assign random fields of study
        $specialties = [
            'Computer Science',
            'Mathematics',
            'Physics',
            'English Literature',
            'Chemistry',
            'History',
            'Biology',
            'Business Administration'
        ];

        // 2. Loop through each teacher and assign random relations
        foreach ($teachers as $teacher) {
            $branchId = $teacher->school_branch_id;

            // --- SEED LEVELS ---
            // Pick between 1 and 3 random level IDs
            $randomLevels = $levels->random(rand(1, 3));
            $levelsSyncData = [];

            foreach ($randomLevels as $level) {
                $levelsSyncData[$level->id] = [
                    'school_branch_id' => $branchId
                ];
            }
            $teacher->levels()->sync($levelsSyncData);


            // --- SEED QUALIFICATIONS ---
            // Pick between 1 and 2 random qualification IDs
            $randomQualifications = $qualifications->random(rand(1, 2));
            $qualificationsSyncData = [];

            foreach ($randomQualifications as $qualification) {
                $qualificationsSyncData[$qualification->id] = [
                    'school_branch_id' => $branchId,
                    'field_of_study'   => $specialties[array_rand($specialties)] // Pick a random specialty
                ];
            }
            $teacher->qualifications()->sync($qualificationsSyncData);
        }

        $this->command->info('Teachers successfully linked with random levels and qualifications!');
    }
}
