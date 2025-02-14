<?php

namespace Database\Seeders;

use App\Models\Specialty;
use App\Models\Teacher;
use App\Models\TeacherSpecailtyPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Faker\Factory as Faker;

class InstructorPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolBranch = "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620";
        $teacherIds = Teacher::where("school_branch_id", $schoolBranch)->pluck("id")->toArray();
        $specialtyIds = Specialty::where("school_branch_id", $schoolBranch)->pluck("id")->toArray();


        // Create 500 records
        for ($i = 0; $i < 1000; $i++) {
            // Generate a random teacher ID
            $teacherId = Arr::random($teacherIds);
            $specialtyId = Arr::random($specialtyIds);

            TeacherSpecailtyPreference::create([
                "school_branch_id" => $schoolBranch,
                "teacher_id" => $teacherId,
                "specialty_id" => $specialtyId,
            ]);
        }
    }
}
