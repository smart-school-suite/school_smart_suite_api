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
use App\Models\Permission;
use App\Models\Educationlevels;
use App\Models\Department;
use App\Models\Studentbatch;
use App\Models\Student;
use App\Models\Specialty;
use Exception;
use App\Models\PermissionCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class StarterPackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->createRoles();
        $this->permissionCategorySeeder();
        $this->createPermissions();
        $this->createLetterGrades();
        $this->createGradeCategory();
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
        $this->createDepartments($schoolBranchId);
        $this->createSpecialties();
        $this->createStudentBatch($schoolBranchId);
        $this->createStudents();
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
    private function createExamType(): void
    {
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
    private function createSemesters(): void
    {
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
    private function createLevel(): void
    {
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
    private function createGradesConfig(string $schoolBranchId)
    {
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
    private function createParents(string $schoolBranchId)
    {
        $faker = Faker::create();
        for ($i = 0; $i <= 20; $i++) {
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
    private function createPermissions()
    {
        try {
            DB::beginTransaction();
            $this->command->info("Permission Seeding has begun");
            $studentMananger = PermissionCategory::where("title", "Student Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.student.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.mark.student.as.dropout",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.reinstate.dropout.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.view.student.dropout",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.delete.student.dropout",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.view.student.details",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.view.students",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.delete.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.promote",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);

            //student batch mananger permissions
            $studentBatchMananger = PermissionCategory::where("title", "Student Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.student.batch.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.view.graduation.dates",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.create.graduation.dates",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);

            //specailty Permissions
            $specialtyMananger = PermissionCategory::where("title", "Specialty Manager")->firstOrFail();

            Permission::create([
                'name' => "schoolAdmin.specialty.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.show.details",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.avialability.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);

            //hos permissions
            $hosMananger = PermissionCategory::where("title", "HOS Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.hos.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hosMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hos.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hosMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hos.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hosMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hos.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hosMananger->id
            ]);

            //teacher permission
            $teacherMananager = PermissionCategory::where("title", "Teacher Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.teacher.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.view.time.timetable",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.add.specialty.peference",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.view.specialty.peference",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "teacher.teacher.view.specialty.peference",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);

            //availability Permissions Starts here
            Permission::create([
                'name' => "teacher.avialability.view",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.avialability.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "teacher.avialability.create",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "teacher.avialability.show.teacher",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "teacher.avialability.delete",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "teacher.avialability.update",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);

            //Exam Manager
            $examMananger = PermissionCategory::where("title", "Exam Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.exam.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "student.exam.show",
                "guard_name" => "student",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "teacher.exam.show",
                "guard_name" => "teacher",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.add.grade.config",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.view.accessed.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.view.letter.grades",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.candidate.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.candidate.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.timetable.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.timetable.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.timetable.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.timetable.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.timetable.course.data",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            //exam results

            Permission::create([
                'name' => "schoolAdmin.examResults.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.examResults.view.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "teacher.examResults.view.student",
                "guard_name" => "teacher",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "student.examResults.view.student",
                "guard_name" => "student",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.examResults.view.standings",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);

            //exam permissions
            Permission::create([
                'name' => "appAdmin.examType.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.examType.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.examType.view",
                "guard_name" => "appAdmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.examType.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.examType.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $examMananger->id
            ]);

            //resit exam Mananger
            Permission::create([
                'name' => "schoolAdmin.resitExam.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.resitExam.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.resitExam.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.resitExam.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.resitExam.add.grading",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);

            $electionMananger = PermissionCategory::where("title", "Election Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.election.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view.candidates",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.add.participants",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view.participants",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.vote",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view.results",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view.past.winners",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view.winners.current",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);

            //election type permissions
            Permission::create([
                'name' => "schoolAdmin.electionType.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.view.active",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);

            //election Applications
            Permission::create([
                'name' => "schoolAdmin.electionApplications.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "student.electionApplications.create",
                "guard_name" => "student",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionApplications.view.elections",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "student.electionApplications.update",
                "guard_name" => "student",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionApplications.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionApplications.approve",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);

            //electionTypes Permissions
            Permission::create([
                'name' => "schoolAdmin.electionRole.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "student.electionRole.view.election",
                "guard_name" => "student",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.view.election",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "student.electionRole.view.active.election",
                "guard_name" => "student",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);

            $resitMananger = PermissionCategory::where("title", "Resit Manager")->firstOrFail();
            Permission::create([
                'name' => "student.studentResits.view.student",
                "guard_name" => "student",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.pay",
                "guard_name" => "student",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.transactions.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.transactions.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.transaction.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.transaction.reverse",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.store.scores",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.update.scores",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view.evaluation.data",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view.student.resitExam",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view.eligable.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view.eligable.student.resitExam",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.timetable.resitexam.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.timetable.resitexam.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.timetable.resitexam.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.timetable.resitexam.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.timetable.resitexam.courses.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            $courseMananger = PermissionCategory::where("title", "Course Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.course.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.view.active",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);

            $countryMananger = PermissionCategory::where("title", "Country Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.country.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $countryMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.country.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $countryMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.country.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $countryMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.country.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $countryMananger->id
            ]);

            $departmentMananger = PermissionCategory::where("title", "Department Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.department.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);

            $hodManager = PermissionCategory::where("title", "HOD Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.hod.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hodManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hod.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hodManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hod.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hodManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hod.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hodManager->id
            ]);

            $eventManager = PermissionCategory::where("title", "Event Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.event.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.event.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.event.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.event.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "student.event.view",
                "guard_name" => "student",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "teacher.event.view",
                "guard_name" => "teacher",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "teacher.event.show",
                "guard_name" => "teacher",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "student.event.show",
                "guard_name" => "student",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.event.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $eventManager->id
            ]);

            $additionalFeeManager = PermissionCategory::where("title", "Additional Fee Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.additionalFee.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.view.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "student.additionalFee.view.student",
                "guard_name" => "student",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.pay",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.transactions.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.transactions.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.transactions.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.transactions.reverse",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);

            //additional Fee Category Permissions
            Permission::create([
                'name' => "schoolAdmin.additionalFeeCategory.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFeeCategory.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFeeCategory.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFeeCategory.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);

            $tuitionFeeManager = PermissionCategory::where("title", "Tuition Fee Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.pay",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.view.paid",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.view.deptors",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.view.transactions",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.show.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.reverse.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.delete.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);

            $registrationFeeManager = PermissionCategory::where("title", "Registration Fee Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.registrationFee.pay",
                "guard_name" => "schooladmin",
                "permission_category_id" => $registrationFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.registrationFee.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $registrationFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.registrationFee.view.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $registrationFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.registrationFee.delete.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $registrationFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.registrationFee.reverse.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $registrationFeeManager->id
            ]);

            //Fee Payment Schedule Permissions
            Permission::create([
                'name' => "schoolAdmin.feeSchedule.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeSchedule.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeSchedule.view.specialty",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeSchedule.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeSchedule.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);

            //Fee Waiver Permissions
            Permission::create([
                'name' => "schoolAdmin.feeWaiver.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeWaiver.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeWaiver.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeWaiver.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeWaiver.view.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);


            //grade permissions
            $gradeManager = PermissionCategory::where("title", "Grades Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.grades.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.update.grade.config",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.relatedexam.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.config.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);

            //grade category
            Permission::create([
                'name' => "appAdmin.gradesCategory.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.gradesCategory.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.gradesCategory.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.gradesCategory.view",
                "guard_name" => "appAdmin",
                "permission_category_id" => $gradeManager->id
            ]);

            //letter grade manager
            $letterGradeManager = PermissionCategory::where("title", "Letter Grade Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.letterGrade.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $letterGradeManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.letterGrade.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $letterGradeManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.letterGrade.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $letterGradeManager->id
            ]);
            //create level manager
            $levelManager = PermissionCategory::where("title", "Level Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.level.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $levelManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.level.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $levelManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.level.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $levelManager->id
            ]);

            //semester Manager
            $semesterManager = PermissionCategory::where("title", "Semester Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.semester.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.semester.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.semester.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $semesterManager->id
            ]);

            //school Semester
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.view.active",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);

            $ratesManager = PermissionCategory::where("title", "Rate Card Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.rateCard.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $ratesManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.rateCard.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $ratesManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.rateCard.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $ratesManager->id
            ]);

            $subscriptionManager = PermissionCategory::where("title", "Subscription Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.subscription.view.subscribed.schools",
                "guard_name" => "appAdmin",
                "permission_category_id" => $subscriptionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.subscription.show.schoolBranch",
                "guard_name" => "schooladmin",
                "permission_category_id" => $subscriptionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.subscription.delete.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $subscriptionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.subscription.view.transactions.schoolBranch",
                "guard_name" => "schooladmin",
                "permission_category_id" => $subscriptionManager->id
            ]);

            $schoolExpensesManager = PermissionCategory::where("title", "School Expenses Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);

            //expenses category permissions
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.category.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.category.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.category.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.category.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);

            $schoolAdminManager = PermissionCategory::where("title", "School Admin Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.upload.avatar",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.delete.avatar",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);

            $schoolBranchManager = PermissionCategory::where("title", "School Branch Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.schoolBranch.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $schoolBranchManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolBranch.update",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $schoolBranchManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.schoolBranch.view",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $schoolBranchManager->id
            ]);

            $schoolManager = PermissionCategory::where("title", "School Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.school.show",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $schoolManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.school.update",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $schoolManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.school.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $schoolManager->id
            ]);

            $roleManager = PermissionCategory::where("title", "Role Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.role.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $roleManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.role.update",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $roleManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.role.create",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $roleManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.role.assign",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $roleManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.role.remove",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $roleManager->id
            ]);

            $permissionManager = PermissionCategory::where("title", "Permission Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.permission.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $permissionManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.permission.create",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $permissionManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.permission.update",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $permissionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.permission.view.schoolAdmin",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $permissionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.permission.assign",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $permissionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.permission.remove",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $permissionManager->id
            ]);

            $parentManager = PermissionCategory::Where("title", "Parent Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.parent.create",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $parentManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.parent.update",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $parentManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.parent.view",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $parentManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.parent.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $parentManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.parent.show",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $parentManager->id
            ]);


            $marksManager = PermissionCategory::where("title", "Marks Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.mark.create.ca.marks",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.update.ca.marks",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.create.exam.marks",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.update.exam.marks",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.view.accessed.courses",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.view.ca.evaluation.data",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.view.exam.evaluation.data",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.view.ca.result.data",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.view.student",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            DB::commit();
            $this->command->info("Permissions Created Successfully");
        } catch (Exception $e) {
            DB::rollback();
            $this->command->info($e->getMessage());
        }
    }
    private function createRoles()
    {
        Role::create(['uuid' =>  Str::uuid(), "name" => "teacher", "guard_name" => "teacher"]);
        Role::create(['uuid' =>  Str::uuid(), "name" => "schoolAdmin", "guard_name" => "schooladmin"]);
        Role::create(['uuid' =>  Str::uuid(), "name" => "student", "guard_name" => "student"]);
        Role::create(['uuid' => Str::uuid(), "name" => "appSuperAdmin", "guard_name" => "appAdmin"]);
        Role::create(['uuid' => Str::uuid(), "name" => "appAdmin", "guard_name" => "appAdmin"]);
        Role::Create(['uuid' =>  Str::uuid(), "name" => "schoolSuperAdmin", "guard_name" => "schooladmin"]);
    }
    private function createGradeCategory()
    {
        // Define the categories
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

        // Insert the categories into the database
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
            throw new \Exception("CSV file not found or not readable!");
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

    private function permissionCategorySeeder()
    {
        $data = [
            [
                "title" => "Student Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Level Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "School Admin Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Subscription Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Role Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Permission Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Rate Card Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Event Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Registration Fee Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Parent Manager",
                "description" => "Manages parent profiles, contact information, and related communications."
            ],
            [
                "title" => "Additional Fee Manager",
                "description" => "Manages extra fees beyond standard tuition, such as lab or activity fees."
            ],
            [
                "title" => "Additional Fee Category Manager",
                "description" => "Organizes and categorizes various types of additional fees."
            ],
            [
                "title" => "Country Manager",
                "description" => "Handles country-specific settings, regions, and locale configurations."
            ],
            [
                "title" => "Course Manager",
                "description" => "Manages course creation, updates, and curriculum details."
            ],
            [
                "title" => "Election Manager",
                "description" => "Oversees election processes, candidate registration, and voting procedures."
            ],
            [
                "title" => "Election Application Manager",
                "description" => "Handles applications related to elections, candidate submissions, and approvals."
            ],
            [
                "title" => "App Admin Manager",
                "description" => "Provides administrative access to manage app settings and configurations."
            ],
            [
                "title" => "School Events Manager",
                "description" => "Coordinates school events, schedules, and related notifications."
            ],
            [
                "title" => "Exam Manager",
                "description" => "Organizes exams, schedules, and manages exam results."
            ],
            [
                "title" => "Tuition Fee Manager",
                "description" => "Manages tuition fee structures, payments, and billing."
            ],
            [
                "title" => "Grades Manager",
                "description" => "Handles student grades, grade entries, and reporting."
            ],
            [
                "title" => "Grades Category Manager",
                "description" => "Organizes grades into categories such as exams, assignments, or projects."
            ],
            [
                "title" => "HOD Manager",
                "description" => "Heads department management, overseeing faculty and department activities."
            ],
            [
                "title" => "HOS Manager",
                "description" => "Manages school heads or principals and their administrative duties."
            ],
            [
                "title" => "Letter Grade Manager",
                "description" => "Sets and manages letter grade scales and grading criteria."
            ],
            [
                "title" => "Marks Manager",
                "description" => "Records and manages student marks and assessments."
            ],
            [
                "title" => "Resit Manager",
                "description" => "Handles resit exams and related scheduling."
            ],
            [
                "title" => "Registration Fee Manager",
                "description" => "Manages registration fee collection and status tracking."
            ],
            [
                "title" => "School Manager",
                "description" => "Oversees overall school information, settings, and operations."
            ],
            [
                "title" => "School Branch Manager",
                "description" => "Manages different branches or campuses of the school."
            ],
            [
                "title" => "School Semester Manager",
                "description" => "Handles semester schedules, durations, and academic periods."
            ],
            [
                "title" => "Semester Manager",
                "description" => "Organizes and manages semester-specific data and activities."
            ],
            [
                "title" => "School Expenses Manager",
                "description" => "Tracks and manages school-related expenses and budgets."
            ],
            [
                "title" => "Specialty Manager",
                "description" => "Handles specialized programs, courses, or departments within the school."
            ],
            [
                "title" => "Student Results Manager",
                "description" => "Handles processing, storage, and retrieval of student results."
            ],
            [
                "title" => "Department Manager",
                "description" => "Handles processing, storage, and retrieval of student results."
            ],
            [
                "title" => "Subscription Payment Manager",
                "description" => "Manages subscription plans, payments, and billing cycles."
            ],
            [
                "title" => "Teacher Manager",
                "description" => "Handles teacher profiles, assignments, and schedules."
            ],
        ];
        foreach ($data as $entry) {
            PermissionCategory::create([
                'title' => $entry['title'],
                'description' => $entry['description']
            ]);
        }

        $this->command->info("Permission Category Seeded Successfully");
    }
    private function createDepartments($schoolBranchId){
        $departments = [
    [
        'department_name' => 'Computer Science',
        'description' => 'The Department of Computer Science offers undergraduate and postgraduate programs focused on software development, algorithms, and data systems.',
        'school_branch_id' => $schoolBranchId
    ],
    [
        'department_name' => 'Mathematics',
        'description' => 'The Department of Mathematics provides courses and research opportunities in pure and applied mathematics, including calculus, algebra, and statistics.',
        'school_branch_id' => $schoolBranchId
    ],
    [
        'department_name' => 'Physics',
        'description' => 'The Department of Physics explores fundamental principles of matter and energy through theoretical and experimental research.',
        'school_branch_id' => $schoolBranchId
    ],
    [
        'department_name' => 'Chemistry',
        'description' => 'The Department of Chemistry offers programs in organic, inorganic, and physical chemistry, emphasizing laboratory skills and research.',
        'school_branch_id' => $schoolBranchId
    ],
    [
        'department_name' => 'Biology',
        'description' => 'The Department of Biology focuses on the study of living organisms, ecology, genetics, and molecular biology with research opportunities.',
        'school_branch_id' => $schoolBranchId
    ],
    [
        'department_name' => 'Engineering',
        'description' => 'The Department of Engineering provides comprehensive programs in civil, mechanical, electrical, and software engineering, emphasizing practical skills and innovation.',
        'school_branch_id' => $schoolBranchId
    ],
    [
        'department_name' => 'Health Science',
        'description' => 'The Department of Health Science offers courses and research in public health, nursing, biomedical sciences, and healthcare management.',
        'school_branch_id' => $schoolBranchId
    ]
];
        foreach ($departments as $department) {
            Department::create($department);
        }
    }
    private function createSpecialties()
    {
        $departments = Department::all();
        $levels = Educationlevels::all();
        $specialtyNames = [
            'Computer Science' => [
                'Software Engineering', 'Data Science', 'Cybersecurity', 'Artificial Intelligence', 'Networking'
            ],
            'Mathematics' => [
                'Applied Mathematics', 'Pure Mathematics', 'Statistics', 'Financial Mathematics', 'Actuarial Science'
            ],
            'Physics' => [
                'Astrophysics', 'Nuclear Physics', 'Quantum Mechanics', 'Solid State Physics', 'Theoretical Physics'
            ],
            'Chemistry' => [
                'Organic Chemistry', 'Inorganic Chemistry', 'Physical Chemistry', 'Analytical Chemistry', 'Biochemistry'
            ],
            'Biology' => [
                'Genetics', 'Ecology', 'Microbiology', 'Molecular Biology', 'Environmental Science'
            ],
            'Engineering' => [
                'Civil Engineering', 'Mechanical Engineering', 'Electrical Engineering', 'Chemical Engineering', 'Computer Engineering'
            ],
            'Health Science' => [
                'Public Health', 'Nursing Science', 'Biomedical Science', 'Health Informatics', 'Nutrition and Dietetics'
            ]
        ];

        foreach ($departments as $department) {
            $this->command->info("Seeding specialties for Department: $department->name");
            $departmentSpecialties = $specialtyNames[$department->name] ?? [
                'General Specialty A', 'General Specialty B', 'General Specialty C', 'General Specialty D', 'General Specialty E'
            ];

            shuffle($departmentSpecialties);
            $selectedSpecialties = array_slice($departmentSpecialties, 0, 5);

            foreach ($selectedSpecialties as $specialtyName) {
               foreach($levels as $level){
                 $registrationFee = rand(5000, 15000) * 10;
                $schoolFee = rand(100000, 500000) * 10;
                Specialty::create([
                    'department_id'    => $department->id,
                    'specialty_name'   => $specialtyName,
                    'registration_fee' => $registrationFee,
                    'school_fee'       => $schoolFee,
                    'level_id'         => $level->id,
                    'status'           => 'active',
                    'description'      => "A comprehensive program in {$specialtyName} within the {$department->name} department, designed to equip students with advanced skills.",
                    'school_branch_id' => $department->school_branch_id,
                ]);
               }
            }
        }
    }
    private function createStudentBatch($schoolBranchId){
       StudentBatch::create([
           'name' => "batch of greate archievements",
           "description" => "The Batch of Great Achievements stands as a testament to dedication, resilience, and excellence. Comprising individuals who have relentlessly pursued their goals, this group has set new standards of success through innovation, hard work, and unwavering commitment. Their collective accomplishments not only reflect personal excellence but also inspire future generations to strive for greatness. This batch symbolizes the pinnacle of perseverance and the transformative power of determination, leaving a lasting legacy of achievement and excellence",
           "status" => "active",
           "school_branch_id" => $schoolBranchId
       ]);
    }
     private function createStudents()
    {
        $specialties = Specialty::all();
        $levels = EducationLevels::all();
        $guardians = Parents::all();
        $studentBatches = StudentBatch::all();
        $genders = ['Male', 'Female'];
        $accountStatuses = ['active','inactive'];
        $paymentFormats = ['one time','installmental'];

        foreach ($specialties as $specialty) {
            $this->command->info("Seeding students for Specialty: " . $specialty->specialty_name);
            $specialtyLevel = $levels->where('id', $specialty->level_id)->first();
            if (!$specialtyLevel) {
                $this->command->error("Level not found for specialty ID: {$specialty->id}. Skipping students for this specialty.");
                continue;
            }

            for ($i = 0; $i < 5; $i++) {
                $firstName = Faker::create()->firstName();
                $lastName = Faker::create()->lastName();
                $fullName = $firstName . ' ' . $lastName;
                $gender = $genders[array_rand($genders)];
                $dob = Faker::create()->dateTimeBetween('-20 years', '-15 years')->format('Y-m-d');
                $phoneOne = Faker::create()->unique()->phoneNumber();
                $phoneTwo = ($i % 2 == 0) ? Faker::create()->unique()->phoneNumber() : null;
                $email = Str::slug($firstName . '.' . $lastName . rand(1, 99)) . '@student.com';
                $password = Hash::make('password');
                $paymentFormat = $paymentFormats[array_rand($paymentFormats)];

                Student::create([
                    'id' => Str::uuid()->toString(),
                    'name'             => $fullName,
                    'first_name'       => $firstName,
                    'last_name'        => $lastName,
                    'DOB'              => $dob,
                    'gender'           => $gender,
                    'phone_one'        => $phoneOne,
                    'phone_two'        => $phoneTwo,
                    'level_id'         => $specialtyLevel->id,
                    'school_branch_id' => $specialty->school_branch_id,
                    'specialty_id'     => $specialty->id,
                    'department_id'    => $specialty->department_id,
                    'guardian_id'      => $guardians->random()->id,
                    'student_batch_id' => $studentBatches->random()->id,
                    'payment_format'   => $paymentFormat,
                    'email'            => $email,
                    'password'         => $password,
                    'profile_picture'  => 'https://i.pravatar.cc/150?img=' . rand(1, 70), // Mock profile picture
                ]);
            }
        }
    }

}
