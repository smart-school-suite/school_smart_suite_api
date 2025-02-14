<?php

namespace Database\Seeders;

use App\Models\SchoolSemester;
use App\Models\Semester;
use App\Models\Specialty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Faker\Factory as Faker;

class SchoolSemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesters = Semester::whereBetween('count', [1, 2])->pluck('id')->toArray();
        $schoolBranch = "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620";
        $specialties = Specialty::where("school_branch_id", $schoolBranch)->pluck('id')->toArray();

        $faker = Faker::create();

        // Create 10 records
        for ($i = 0; $i < 100; $i++) {
            $schoolYearStart = $faker->numberBetween(2020, 2025);
            $schoolYearEnd = $schoolYearStart + $faker->numberBetween(0, 2); // Ending within 0-2 years after the start year

             SchoolSemester::create([
                'school_branch_id' => $schoolBranch,
                'specialty_id' => Arr::random($specialties),
                'start_date' => $faker->dateTimeBetween("{$schoolYearStart}-01-01", "{$schoolYearStart}-06-30")->format('Y-m-d'),
                'end_date' => $faker->dateTimeBetween("{$schoolYearStart}-07-01", "{$schoolYearEnd}-12-31")->format('Y-m-d'),
                'school_year_start' => $schoolYearStart,
                'school_year_end' => $schoolYearEnd,
                'semester_id' => Arr::random($semesters),
            ]);

            // Optional: You can log the created record for verification or debugging
            // \Log::info('Created School Semester:', $schoolSemester->toArray());
        }
    }
}
