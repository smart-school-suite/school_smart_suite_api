<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Educationlevels;
use App\Models\Schoolbranches;
use App\Models\Specialty;
use App\Models\Studentbatch;
use App\Models\Semester;
use App\Models\Parents;
use App\Models\Student;
use App\Models\RegistrationFee;
use App\Models\TuitionFees;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class FakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->seedParent();
        $this->seedStudentBatch();
        $this->seedDepartment();
        $this->seedSpecialty();
        $this->seedTeacher();
        $this->seedCourse();
        $this->seedStudent();
        $this->seedStudentFees();
    }

    public function seedDepartment()
    {
        $schoolBranch = Schoolbranches::first();
        $timestamp = now();

        $departments = [
            ['name' => 'Computer Science', 'description' => 'Department focusing on computing technologies and software development.'],
            ['name' => 'Electrical Engineering', 'description' => 'Department dedicated to electrical systems and electronics.'],
            ['name' => 'Mechanical Engineering', 'description' => 'Department specializing in mechanical systems and design.'],
            ['name' => 'Civil Engineering', 'description' => 'Department focused on infrastructure and construction.'],
            ['name' => 'Chemical Engineering', 'description' => 'Department exploring chemical processes and materials.'],
            ['name' => 'Mathematics', 'description' => 'Department dedicated to pure and applied mathematics.'],
            ['name' => 'Physics', 'description' => 'Department studying physical sciences and phenomena.'],
            ['name' => 'Biology', 'description' => 'Department focusing on life sciences and organisms.'],
            ['name' => 'Chemistry', 'description' => 'Department specializing in chemical reactions and properties.'],
            ['name' => 'Business Administration', 'description' => 'Department covering management and business operations.'],
            ['name' => 'Economics', 'description' => 'Department analyzing economic systems and policies.'],
            ['name' => 'English Literature', 'description' => 'Department exploring literary works and criticism.'],
            ['name' => 'History', 'description' => 'Department studying historical events and cultures.'],
            ['name' => 'Political Science', 'description' => 'Department focusing on government and political systems.'],
            ['name' => 'Psychology', 'description' => 'Department studying human behavior and mental processes.'],
            ['name' => 'Sociology', 'description' => 'Department analyzing social structures and interactions.'],
            ['name' => 'Environmental Science', 'description' => 'Department focusing on environmental systems and sustainability.'],
            ['name' => 'Architecture', 'description' => 'Department dedicated to architectural design and planning.'],
            ['name' => 'Pharmacy', 'description' => 'Department specializing in pharmaceutical sciences.'],
            ['name' => 'Nursing', 'description' => 'Department focusing on healthcare and patient care.'],
        ];

        $departmentData = [];
        foreach ($departments as $dept) {
            $uuid = Str::uuid()->toString();
            $departmentData[] = [
                'id' => $uuid,
                'department_name' => $dept['name'],
                'description' => $dept['description'],
                'school_branch_id' => $schoolBranch->id,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if (!empty($departmentData)) {
            DB::table('department')->insert($departmentData);
            $this->command->info("20 Departments Inserted Successfully");
        } else {
            $this->command->info("No Departments To Insert");
        }
    }
    public function seedSpecialty()
    {
        $faker = Faker::create();
        $schoolBranch = Schoolbranches::first();

        if (!$schoolBranch) {
            $this->command->info("Please ensure School Branches are seeded first.");
            return;
        }

        $timestamp = now();
        $departments = Department::where('school_branch_id', $schoolBranch->id)->get();

        if ($departments->isEmpty()) {
            $this->command->info("No departments found for this school branch. Please seed departments first.");
            return;
        }

        $specialtyTemplates = [
            'Computer Science' => [
                ['name' => 'Software Engineering', 'description' => 'Focus on software design and development.'],
                ['name' => 'Cybersecurity', 'description' => 'Study of securing digital systems.'],
                ['name' => 'Data Science', 'description' => 'Analysis and interpretation of complex data.'],
                ['name' => 'Artificial Intelligence', 'description' => 'Development of intelligent systems.'],
            ],
            'Electrical Engineering' => [
                ['name' => 'Power Systems', 'description' => 'Study of electrical power generation and distribution.'],
                ['name' => 'Electronics', 'description' => 'Design and application of electronic circuits.'],
                ['name' => 'Telecommunications', 'description' => 'Focus on communication systems.'],
            ],
            'Mechanical Engineering' => [
                ['name' => 'Robotics', 'description' => 'Design and development of robotic systems.'],
                ['name' => 'Thermodynamics', 'description' => 'Study of energy and heat transfer.'],
                ['name' => 'Automotive Engineering', 'description' => 'Design of automotive systems.'],
            ],
            'Civil Engineering' => [
                ['name' => 'Structural Engineering', 'description' => 'Design of buildings and structures.'],
                ['name' => 'Geotechnical Engineering', 'description' => 'Study of soil and rock mechanics.'],
                ['name' => 'Transportation Engineering', 'description' => 'Planning and design of transportation systems.'],
            ],
            'Chemical Engineering' => [
                ['name' => 'Process Engineering', 'description' => 'Design of chemical processes.'],
                ['name' => 'Biochemical Engineering', 'description' => 'Application of engineering to biological processes.'],
            ],
            'Mathematics' => [
                ['name' => 'Applied Mathematics', 'description' => 'Mathematical applications in science and engineering.'],
                ['name' => 'Statistics', 'description' => 'Study of data analysis and probability.'],
            ],
            'Physics' => [
                ['name' => 'Quantum Physics', 'description' => 'Study of quantum mechanics and phenomena.'],
                ['name' => 'Astrophysics', 'description' => 'Study of celestial bodies and the universe.'],
            ],
            'Biology' => [
                ['name' => 'Molecular Biology', 'description' => 'Study of biological processes at the molecular level.'],
                ['name' => 'Ecology', 'description' => 'Study of ecosystems and environmental interactions.'],
            ],
            'Chemistry' => [
                ['name' => 'Organic Chemistry', 'description' => 'Study of carbon-based compounds.'],
                ['name' => 'Analytical Chemistry', 'description' => 'Techniques for chemical analysis.'],
            ],
            'Business Administration' => [
                ['name' => 'Finance', 'description' => 'Study of financial management and investments.'],
                ['name' => 'Marketing', 'description' => 'Strategies for market analysis and promotion.'],
                ['name' => 'Human Resource Management', 'description' => 'Management of organizational personnel.'],
            ],
            'Economics' => [
                ['name' => 'Microeconomics', 'description' => 'Study of individual markets and decision-making.'],
                ['name' => 'Macroeconomics', 'description' => 'Analysis of national and global economies.'],
            ],
            'English Literature' => [
                ['name' => 'Modern Literature', 'description' => 'Study of contemporary literary works.'],
                ['name' => 'Classical Literature', 'description' => 'Analysis of historical literary texts.'],
            ],
            'History' => [
                ['name' => 'World History', 'description' => 'Study of global historical events.'],
                ['name' => 'Cultural History', 'description' => 'Analysis of cultural developments through history.'],
            ],
            'Political Science' => [
                ['name' => 'International Relations', 'description' => 'Study of global political interactions.'],
                ['name' => 'Public Policy', 'description' => 'Analysis of government policies and their impact.'],
            ],
            'Psychology' => [
                ['name' => 'Clinical Psychology', 'description' => 'Study of mental health and therapy.'],
                ['name' => 'Cognitive Psychology', 'description' => 'Study of mental processes like memory and perception.'],
            ],
            'Sociology' => [
                ['name' => 'Social Theory', 'description' => 'Study of sociological theories and frameworks.'],
                ['name' => 'Urban Sociology', 'description' => 'Analysis of urban communities and structures.'],
            ],
            'Environmental Science' => [
                ['name' => 'Conservation Biology', 'description' => 'Study of biodiversity preservation.'],
                ['name' => 'Environmental Policy', 'description' => 'Analysis of environmental regulations.'],
            ],
            'Architecture' => [
                ['name' => 'Urban Design', 'description' => 'Planning and design of urban spaces.'],
                ['name' => 'Sustainable Architecture', 'description' => 'Design of eco-friendly buildings.'],
            ],
            'Pharmacy' => [
                ['name' => 'Clinical Pharmacy', 'description' => 'Study of patient-centered pharmaceutical care.'],
                ['name' => 'Pharmacology', 'description' => 'Study of drug actions and effects.'],
            ],
            'Nursing' => [
                ['name' => 'Pediatric Nursing', 'description' => 'Care for pediatric patients.'],
                ['name' => 'Critical Care Nursing', 'description' => 'Nursing in intensive care settings.'],
            ],
        ];

        $specialties = [];
        $levels = Educationlevels::all();

        foreach ($departments as $department) {
            $specialtyOptions = $specialtyTemplates[$department->department_name] ?? [
                ['name' => 'General Specialization', 'description' => 'General study within the department.'],
            ];
            $numSpecialties = rand(2, 5);
            $selectedSpecialties = array_slice($specialtyOptions, 0, $numSpecialties);

            foreach ($selectedSpecialties as $spec) {
                foreach ($levels as $level) {
                    $specialties[] = [
                        'id' => Str::uuid(),
                        'specialty_name' => $spec['name'],
                        'description' => $spec['description'],
                        'registration_fee' => $faker->numberBetween(50000, 150000),
                        'school_fee' => $faker->numberBetween(100000, 1000000),
                        'department_id' => $department->id,
                        'level_id' => $level->id,
                        'school_branch_id' => $schoolBranch->id,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }
        }

        if (!empty($specialties)) {
            $chunks = array_chunk($specialties, 1000);
            foreach ($chunks as $chunk) {
                DB::table('specialty')->insert($chunk);
            }
            $this->command->info("Specialties inserted successfully.");
        } else {
            $this->command->info("No specialties to insert.");
        }
    }
    public function seedTeacher()
    {
        $faker = Faker::create();
        $schoolBranch = Schoolbranches::first();
        $timestamp = now();
        $teachers = [];

        for ($i = 0; $i < 100; $i++) {
            $uuid = Str::uuid()->toString();
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $gender = $faker->randomElement(['Male', 'Female']);
            $phoneOne = $faker->phoneNumber;
            $phoneTwo = $faker->boolean(50) ? $faker->phoneNumber : null;

            $teachers[] = [
                'id' => $uuid,
                'school_branch_id' => $schoolBranch->id,
                'email' => $faker->unique()->safeEmail,
                'name' => "$firstName $lastName",
                'phone_one' => $phoneOne,
                'phone_two' => $phoneTwo,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address' => $faker->address,
                'password' => Hash::make('password'),
                'gender' => $gender,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if (!empty($teachers)) {
            DB::table('teacher')->insert($teachers);
            $this->command->info("100 Teachers Inserted Successfully");
        } else {
            $this->command->info("No Teachers To Insert");
        }
    }
    public function seedParent()
    {
        $faker = Faker::create();
        $schoolBranch = Schoolbranches::first();
        $timestamp = now();
        $parents = [];
        for ($i = 0; $i < 1000; $i++) {
            $uuid = Str::uuid()->toString();
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $gender = $faker->randomElement(['Male', 'Female']);
            $phoneOne = $faker->phoneNumber;
            $phoneTwo = $faker->boolean(50) ? $faker->phoneNumber : null;

            $parents[] = [
                'id' => $uuid,
                'school_branch_id' => $schoolBranch->id,
                'email' => $faker->unique()->safeEmail,
                'name' => "$firstName $lastName",
                'phone_one' => $phoneOne,
                'phone_two' => $phoneTwo,
                'address' => $faker->address,
                'relationship_to_student' => $gender == 'Male' ? 'Father' :  'Mother',
                'preferred_language' => 'english',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if (!empty($parents)) {
            DB::table('parents')->insert($parents);
            $this->command->info("1000 Parents Inserted Successfully");
        } else {
            $this->command->info("No Parents To Insert");
        }
    }
    public function seedStudentBatch()
    {
        $batchesData = [
            [
                "title" => "Batch of Great Achievements",
                "description" => "This batch is celebrated for its outstanding accomplishments and groundbreaking projects that have set new standards of excellence."
            ]
        ];
        $schoolBranch = Schoolbranches::first();
        foreach ($batchesData as $batch) {
            Studentbatch::create([
                'name' => $batch['title'],
                'description' => $batch['description'],
                'school_branch_id' => $schoolBranch->id
            ]);
        }
    }
    public function seedCourse()
    {
        $schoolBranch = Schoolbranches::first();

        $faker = Faker::create();
        $timestamp = now();

        $specialties = Specialty::where('school_branch_id', $schoolBranch->id)->get();

        $semesters = [1, 2];
        $courses = [];

        foreach ($specialties as $specialty) {
            foreach ($semesters as $semester) {
                for ($i = 0; $i < 6; $i++) {
                    $courseCode = strtoupper($faker->lexify('???')) . ' ' . $faker->numerify('###');

                    $courseTitle = $faker->words(rand(2, 5), true);

                    $courseDescription = $faker->paragraph(rand(2, 4));

                    $credit = $faker->numberBetween(1, 6);

                    $courses[] = [
                        'id' => Str::uuid(),
                        'course_code' => $courseCode,
                        'course_title' => ucwords($courseTitle),
                        'specialty_id' => $specialty->id,
                        'department_id' => $specialty->department_id,
                        'school_branch_id' => $specialty->school_branch_id,
                        'credit' => $credit,
                        'description' => $courseDescription,
                        'level_id' => $specialty->level_id,
                        'semester_id' => Semester::where("count", $semester)->first()->id,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }
        }

        if (!empty($courses)) {
            DB::table('courses')->insert($courses);
            $this->command->info("Courses inserted successfully.");
        } else {
            $this->command->info("No courses to insert.");
        }
    }
    public function seedStudent()
    {
        $faker = Faker::create();
        $schoolBranch = Schoolbranches::first();
        $studentBatch = Studentbatch::first();

        if (!$schoolBranch || !$studentBatch) {
            $this->command->info("Please ensure School Branches and Student Batches are seeded first.");
            return;
        }

        $timestamp = now();
        $students = [];
        $specialties = Specialty::where('school_branch_id', $schoolBranch->id)->get();
        $parents = Parents::where("school_branch_id", $schoolBranch->id)->pluck('id')->toArray();

        if (empty($parents)) {
            $this->command->info("No parents found for this school branch. Please seed parents first.");
            return;
        }

        foreach ($specialties as $specialty) {
            for ($i = 0; $i < 50; $i++) {
                $firstName = $faker->firstName;
                $lastName = $faker->lastName;
                $gender = $faker->randomElement(['Male', 'Female']);
                $parentId = $faker->randomElement($parents);
                $dob = $faker->date('Y-m-d', '2005-01-01');

                $students[] = [
                    'id' => Str::uuid(),
                    'student_batch_id' => $studentBatch->id,
                    'specialty_id' => $specialty->id,
                    'department_id' => $specialty->department_id,
                    'level_id' => $specialty->level_id,
                    'guardian_id' => $parentId,
                    'school_branch_id' => $schoolBranch->id,
                    'name' => "$firstName $lastName",
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender' => $gender,
                    'phone_one' => $faker->phoneNumber,
                    'phone_two' => $faker->phoneNumber,
                    'email' => $faker->unique()->safeEmail,
                    'DOB' => $dob,
                    'password' => Hash::make('password'),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        if (!empty($students)) {
            $chunks = array_chunk($students, 1000);
            foreach ($chunks as $chunk) {
                DB::table('student')->insert($chunk);
            }
            $this->command->info("Student inserted successfully.");
        } else {
            $this->command->info("No Student to insert.");
        }
    }
    public function seedStudentFees()
    {
        $schoolBranch = Schoolbranches::first();

        if (!$schoolBranch) {
            $this->command->info("Please ensure School Branches are seeded first.");
            return;
        }

        $students = Student::where("school_branch_id", $schoolBranch->id)
            ->with('specialty')
            ->get();

        if ($students->isEmpty()) {
            $this->command->info("No students found for this school branch. Please seed students first.");
            return;
        }

        $registrationFees = [];
        $tuitionFees = [];
        $timestamp = now();

        foreach ($students as $student) {
            if ($student->specialty) {
                $registrationFees[] = [
                    'id' => Str::uuid(),
                    'student_id' => $student->id,
                    'school_branch_id' => $schoolBranch->id,
                    'specialty_id' => $student->specialty->id,
                    'level_id' => $student->specialty->level_id,
                    'amount' => $student->specialty->registration_fee,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];

                $tuitionFees[] = [
                    'id' => Str::uuid(),
                    'student_id' => $student->id,
                    'school_branch_id' => $schoolBranch->id,
                    'specialty_id' => $student->specialty->id,
                    'level_id' => $student->specialty->level_id,
                    'amount_left' => $student->specialty->school_fee,
                    'tution_fee_total' => $student->specialty->school_fee,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }
        }

        if (!empty($tuitionFees) && !empty($registrationFees)) {
            $regFeeChunks = array_chunk($registrationFees, 1000);
            foreach ($regFeeChunks as $chunk) {
                DB::table('registration_fees')->insert($chunk);
            }
            $tuitionFeeChunks = array_chunk($tuitionFees, 1000);
            foreach ($tuitionFeeChunks as $chunk) {
                DB::table('tuition_fees')->insert($chunk);
            }
            $this->command->info("Student Registration And Tuition Fees Inserted Successfully.");
        } else {
            $this->command->info("No Fees to insert.");
        }
    }
}
