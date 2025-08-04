<?php

namespace Database\Seeders;

use App\Models\GradesCategory;
use App\Models\Semester;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Faker\Factory as Faker;

class StarterPackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try{
        DB::beginTransaction();
        $this->createLetterGrades();
        $this->createGradeCategory();
        $this->seedCountries();
        $this->seedRateCards();
        $this->createLevel();
        $this->createSemesters();
        $this->createExamType();
         DB::commit();
        }
        catch(Exception $e){
           DB::rollBack();
           Log::error($e->getMessage());
        }
    }

    private function seedCountries(): void
    {
        $timestamp = now();
        $filePath = public_path("data/country.csv");

        if (!file_exists($filePath) || !is_readable($filePath)) {
            Log::error("CSV file not found or not readable at: " . $filePath);
            return;
        }

        $countries = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header:', $header ?? []);

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Processing CSV Row:', $data);
                if (count($data) >= 5) {
                    $countries[] = [
                        'id' => Str::uuid()->toString(),
                        'country' => $data[1] ?? null,
                        'code' => $data[2] ?? null,
                        'currency' => $data[3] ?? null,
                        'official_language' => $data[4] ?? null,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                } else {
                    Log::warning('Skipping incomplete CSV row:', $data);
                }
            }
            fclose($handle);
        }

        if (!empty($countries)) {
            DB::table('country')->insert($countries);
            Log::info('Inserted ' . count($countries) . ' countries.');
        } else {
            Log::warning('No countries to insert from CSV.');
        }
    }
    private function createExamType(): void
    {
        Log::info('Exam Type Seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/exam_type.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);

            $exam_type = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                if (count($data) >= 2) {
                    $exam_type[] = [
                        'id' => $uuid,
                        'exam_name' => $data[1],
                        "program_name" => $data[2],
                        "semester" => $data[3],
                        "type" => $data[4],
                        'semester_id' => Semester::where('count', $data['count'])->first()->id,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }

            fclose($handle);

            Log::info('Exam Type Array: ', $exam_type);

            if (!empty($exam_type)) {
                DB::table('exam_type')->insert($exam_type);
                Log::info('Inserted Exam Types: ' . count($exam_type) . ' entries.');
            } else {
                Log::warning('No Exam types to insert.');
            }
        }
    }
    private function createSemesters(): void
    {
        Log::info('Semester seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/semesters.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);

            $semesters = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();

                if (count($data) >= 2) {
                    $semesters[] = [
                        'id' => $uuid,
                        'name' => $data[1],
                        'program_name' => $data[2],
                        'count' => $data[3],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }

            fclose($handle);

            Log::info('Semester Array: ', $semesters);


            if (!empty($semesters)) {
                DB::table('semesters')->insert($semesters);
                Log::info('Inserted semesters: ' . count($semesters) . ' entries.');
            } else {
                Log::warning('No semesters to insert.');
            }
        }
    }
    private function createLevel(): void
    {
        Log::info('Education levels seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/education_levels.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);

            $education_levels = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 10);

                if (count($data) >= 2) {
                    $education_levels[] = [
                        'id' => $id,
                        'name' => $data[1],
                        'level' => $data[2],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }

            fclose($handle);

            Log::info('Education Levels Array: ', $education_levels);


            if (!empty($education_levels)) {
                DB::table('education_levels')->insert($education_levels);
                Log::info('Inserted Education levels: ' . count($education_levels) . ' entries.');
            } else {
                Log::warning('No Education levels to insert.');
            }
        }
    }
    private function seedRateCards(): void
    {
        $faker = Faker::create();
        $rateCards = []; // Use an array for bulk insert
        for ($i = 0; $i < 20; $i++) {
            $rateCards[] = [
                'id' => $faker->uuid,
                'min_students' => $faker->numberBetween(1, 50000),
                'max_students' => $faker->numberBetween(101, 100000),
                'max_school_admins' => $faker->numberBetween(1, 5000),
                'max_teachers' => $faker->numberBetween(1, 5000),
                'monthly_rate_per_student' => $faker->randomFloat(2, 10, 100),
                'yearly_rate_per_student' => $faker->randomFloat(2, 100, 1000),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('rate_cards')->insert($rateCards); // Bulk insert
        Log::info('Created ' . count($rateCards) . ' rate cards.');
    }
    private function createGradeCategory()
    {
        $categories = [
            ['title' => 'Level One CA', 'status' => 'active'],
            ['title' => 'Level One Exam', 'status' => 'active'],
            ['title' => 'Level One Resit', 'status' => 'active'],
            ['title' => 'Level Two CA', 'status' => 'active'],
            ['title' => 'Level Two Exam', 'status' => 'active'],
            ['title' => 'Level Two Resit', 'status' => 'active'],
            ['title' => 'Level Three CA', 'status' => 'active'],
            ['title' => 'Level Three Exam', 'status' => 'active'],
            ['title' => 'Level Three Resit', 'status' => 'active'],
            ['title' => 'Bachelors Degree CA', 'status' => 'active'],
            ['title' => 'Bachelors Degree Exam', 'status' => 'active'],
            ['title' => 'Bachelors Degree Resit', 'status' => 'active'],
            ['title' => 'Masters Degree CA', 'status' => 'active'],
            ['title' => 'Masters Degree Exam', 'status' => 'active'],
            ['title' => 'Masters Degree Resit', 'status' => 'active'],
        ];
        foreach ($categories as $category) {
            GradesCategory::create($category);
        }
    }
    private function createLetterGrades()
    {
        Log::info('LetterGradeTableSeeder has started.');
        $timestamp = now();
        $filePath = public_path("data/letter_grade.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle); // Read the header
            Log::info('CSV Header: ', $header); // Log the header for debugging

            $letter_grade = []; // Initialize an empty array for countries

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data); // Log current row data for debugging
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 10);
                // Ensure the row has at least two columns
                if (count($data) >= 2) {
                    $letter_grade[] = [
                        'id' => $id, // Assign id from 1st column
                        'letter_grade' => $data[1], // Assign name from 2nd column
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }

            fclose($handle);

            Log::info('Letter Grades Array: ', $letter_grade); // Log the countries array after completion

            // Insert the countries into the database
            if (!empty($letter_grade)) {
                DB::table('letter_grade')->insert($letter_grade);
                Log::info('Inserted Grades: ' . count($letter_grade) . ' entries.');
            } else {
                Log::warning('No Grades to insert.');
            }
        }
    }

}
