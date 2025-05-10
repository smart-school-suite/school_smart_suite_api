<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\RatesCard;
use App\Models\School;
use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use App\Models\SchoolBranchApiKey;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionPayment;
use App\Models\GradesCategory;
use App\Models\Parents;
use App\Models\SchoolGradesConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class StarterPackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedCountries();
            $this->seedRateCards();
            $this->createLevel();
            $this->createSemesters();
            $this->createExamType();
            $schoolId = $this->createSchool();
            $schoolBranchId = $this->createSchoolBranch($schoolId);
            $this->createParents($schoolBranchId);
            $this->subscribeSchool($schoolBranchId);
            $this->createSchoolAdmin($schoolBranchId);
            $this->createGradesConfig($schoolBranchId);
        });
    }

    private function seedCountries(): void
    {
        $timestamp = now();
        $filePath = public_path("data/country.csv");

        if (!file_exists($filePath) || !is_readable($filePath)) {
            Log::error("CSV file not found or not readable at: " . $filePath);
            return; // Exit the seeder if the file is not found
        }

        $countries = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle); // Read the header row
            Log::info('CSV Header:', $header ?? []);

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Processing CSV Row:', $data);
                if (count($data) >= 5) { // Ensure we have enough columns
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
    private function createExamType(): void {
        Log::info('Exam Type Seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/exam_type.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);

            $exam_type = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 15);
                $semester = DB::table('semesters')->pluck('id')->toArray();
                $randomSemesterID = Arr::random($semester);

                if (count($data) >= 2) {
                    $exam_type[] = [
                        'id' => $id,
                        'exam_name' => $data[1],
                        "program_name" => $data[2],
                        "semester" => $data[3],
                        "type" => $data[4],
                        'semester_id' => $randomSemesterID,
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
    private function createSemesters(): void {
        Log::info('Semester seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/semesters.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable!");
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);

            $semesters = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 10);

                if (count($data) >= 2) {
                    $semesters[] = [
                        'id' => $id,
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
    private function createLevel(): void {
        Log::info('Education levels seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/education_levels.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable!");
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
    private function createSchool(): string
    {
        $country = Country::where("country", "Cameroon")->firstOrFail();
        $schoolId = Str::uuid()->toString();
        School::create([
            'id' => $schoolId,
            'country_id' => $country->id,
            'name' => 'Experiential Higher Institute',
            'motor' => 'Quality Education for holistic training',
            'type' => 'private',
            'established_year' => '2020-10-10',
        ]);
        Log::info('Created school with ID: ' . $schoolId);
        return $schoolId;
    }
    private function createSchoolBranch(string $schoolId): string
    {
        $faker = Faker::create();
        $schoolBranchId = Str::uuid()->toString();
        Schoolbranches::create([
            'id' => $schoolBranchId,
            'school_id' => $schoolId,
            "branch_name" => "Experiential Higher Institute Yaounde",
            "address" => $faker->address,
            "city" => "Yaounde",
            "state" => "Center",
            "postal_code" => $faker->postcode,
            "phone_one" => $faker->phoneNumber,
            "phone_two" => $faker->phoneNumber,
            "email" => $faker->safeEmail,
            "max_gpa" => 4.00,
            "semester_count" => 2,
            "website" => $faker->url,
            "abbrevaition" => "EXHIST",
        ]);
        Log::info('Created school branch with ID: ' . $schoolBranchId . ' for school ID: ' . $schoolId);
        return $schoolBranchId;
    }
    private function subscribeSchool(string $schoolBranchId): string
    {
        $studentNumber = 10000;
        $rateCard = RatesCard::where('min_students', '<=', $studentNumber)
            ->where('max_students', '>=', $studentNumber)
            ->firstOrFail();
        $totalCost = $rateCard->yearly_rate_per_student * $studentNumber;
        $subscriptionStartDate = Carbon::now();
        $subscriptionEndDate = $subscriptionStartDate->copy()->addYear();

        $subscription = SchoolSubscription::create([
            'school_branch_id' => $schoolBranchId,
            'rate_card_id' => $rateCard->id,
            'subscription_start_date' => $subscriptionStartDate,
            'subscription_end_date' => $subscriptionEndDate,
            'max_number_students' => $studentNumber,
            'max_number_parents' => $studentNumber * 2,
            'max_number_school_admins' => $rateCard->max_school_admins,
            'max_number_teacher' => $rateCard->max_teachers,
            'total_monthly_cost' => null,
            'total_yearly_cost' => $totalCost,
            'billing_frequency' => "yearly",
            'status' => 'active',
        ]);

        $apiKey = Str::random(100);

        SchoolBranchApiKey::create([
            'school_branch_id' => $schoolBranchId,
            'api_key' => $apiKey,
        ]);

        SubscriptionPayment::create([
            'school_subscription_id' => $subscription->id,
            'payment_date' => $subscriptionStartDate,
            'school_branch_id' => $schoolBranchId,
            'amount' => $totalCost,
            'payment_method' => 'card',
            'payment_status' => 'completed',
            'transaction_id' => Str::random(25),
            'description' => 'Subscription payment for school branch ID: ' . $schoolBranchId,
        ]);
        Log::info("Subscribed school branch {$schoolBranchId} with API key: {$apiKey}.");
        return $apiKey;
    }
    private function createSchoolAdmin(string $schoolBranchId): void
    {
        $admin = Schooladmin::create([
            'name' => "chongong precious gemuh",
            'email' => "chongongprecious@gmail.com",
            'first_name' => "chongong",
            'last_name' => "gemuh",
            'school_branch_id' => $schoolBranchId,
            'password' => Hash::make("Keron484$"),
        ]);
        Log::info("Created school admin with email: {$admin->email} for school branch: {$schoolBranchId}");
    }
    private function createGradesConfig(string $schoolBranchId){
        $gradeCategories = GradesCategory::all();
            $configs = $gradeCategories->map(function ($gradeCategory) use ($schoolBranchId) {
                return [
                    'id' => Str::uuid(),
                    'school_branch_id' => $schoolBranchId,
                    'grades_category_id' => $gradeCategory->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            SchoolGradesConfig::insert($configs);
    }
    private function createParents(string $schoolBranchId){
        $faker = Faker::create();
        for($i = 0; $i <= 20; $i++){
            Parents::create([
                'school_branch_id' => $schoolBranchId,
                'name' => $faker->name,
                'address' => $faker->address,
                'email' => $faker->email,
                "phone_one" => $faker->phoneNumber(),
                "phone_two" => $faker->phoneNumber(),
                "relationship_to_student" => "mother",
                "preferred_language" => "english"
            ]);
        }
    }

}

