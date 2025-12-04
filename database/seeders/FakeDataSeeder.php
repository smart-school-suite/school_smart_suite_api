<?php

namespace Database\Seeders;

use App\Models\AdditionalFeesCategory;
use App\Models\AnnouncementCategory;
use App\Models\Schoolexpensescategory;
use App\Models\Department;
use App\Models\Educationlevels;
use App\Models\Schoolbranches;
use App\Models\Specialty;
use App\Models\Studentbatch;
use App\Models\Semester;
use App\Models\Parents;
use App\Models\Student;
use App\Models\EventTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use App\Models\AnnouncementTag;
use App\Models\EventCategory;
use App\Models\Teacher;
use App\Models\TeacherSpecailtyPreference;
use App\Models\Hall;
use App\Models\Courses;
use App\Models\TeacherCoursePreference;
use App\Models\SpecialtyHall;
use Illuminate\Support\Facades\Log;
use App\Models\SchoolSemester;
use App\Models\Timetable;
use Carbon\Carbon;
use App\Models\ElectionType;
use App\Models\ElectionRoles;

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
        $this->seedSchoolAdmin();
        $this->seedAnnouncementTags();

        $this->seedAnnouncementCategories();
        $this->seedEventCategories();
        $this->seedAdditionalFeeCategories();
        $this->seedSchoolExpensesCategories();
        $this->seedEventCategories();
        $this->teacherSpecialtyPreferenceSeeder();
        $this->schoolHallSeeder();
        $this->specialtyHallSeeder();
        $this->teacherCoursePreference();
        $this->electionType();
        $this->electionRoles();
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
            DB::table('departments')->insert($departmentData);
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
        $levels = Educationlevels::whereHas('levelType', function ($query) {
             $query->where("program_name", "level_start_100");
        })->get();

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
                DB::table('specialties')->insert($chunk);
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

            $teachers[] = [
                'id' => $uuid,
                'school_branch_id' => $schoolBranch->id,
                'email' => $faker->unique()->safeEmail,
                'name' => "$firstName $lastName",
                'phone' => $phoneOne,
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
            DB::table('teachers')->insert($teachers);
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

            $parents[] = [
                'id' => $uuid,
                'school_branch_id' => $schoolBranch->id,
                'email' => $faker->unique()->safeEmail,
                'name' => "$firstName $lastName",
                'phone' => $phoneOne,
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
            for ($i = 0; $i < 10; $i++) {
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
                    'phone' => $faker->phoneNumber,
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
                DB::table('students')->insert($chunk);
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
    public function seedSchoolAdmin()
    {
        $faker = Faker::create();
        $schoolBranch = Schoolbranches::first();
        $timestamp = now();
        $schoolAdmins = [];

        for ($i = 0; $i < 200; $i++) {
            $uuid = Str::uuid()->toString();
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;

            $schoolAdmins[] = [
                'id' => $uuid,
                'school_branch_id' => $schoolBranch->id,
                'email' => $faker->unique()->safeEmail,
                'name' => "$firstName $lastName",
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => Hash::make('password'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if (!empty($schoolAdmins)) {
            DB::table('school_admins')->insert($schoolAdmins);
            $this->command->info("200 School Admins Inserted Successfully");
        } else {
            $this->command->info("No School Admins To Insert");
        }
    }
    public function seedAnnouncementTags()
    {
        $announcementTags = [
            'school update',
            'policy change',
            'important info',
            'school rules',
            'new policy',
            'daily notice',
            'weekly update',
            'reminder',
            'staff notice',
            'general info',
            'student notice',
            'urgent update',
            'schedule change',
            'parent notice',
            'bulletin',
            'orientation',
            'staff meeting',
            'guidelines',
            'regulations',
            'general alert',
            'curriculum',
            'syllabus update',
            'course info',
            'assignment notice',
            'project work',
            'study materials',
            'class notes',
            'reading list',
            'new subject',
            'curriculum change',
            'lesson plan',
            'tutorials',
            'class update',
            'learning resources',
            'academic calendar',
            'academic deadline',
            'exam prep',
            'study guide',
            'extra class',
            'course announcement',
            'exam timetable',
            'test schedule',
            'midterm exams',
            'final exams',
            'quiz dates',
            'mock exams',
            'exam routine',
            'exam hall allocation',
            'assessment',
            'oral exams',
            'exam guidelines',
            'time table change',
            'exam venue',
            'lab exams',
            'exam deadlines',
            'exam slots',
            'exam notification',
            'exam start date',
            'resit exams',
            'exam plan',
            'exam results',
            'grading system',
            'report card',
            'GPA update',
            'marks release',
            'assessment results',
            'rank list',
            'result sheet',
            'result publication',
            'results deadline',
            'grading notice',
            'progress report',
            'transcript',
            'performance update',
            'semester results',
            'exam scores',
            'evaluation',
            'student ranking',
            'grade review',
            'academic results',
            'school event',
            'annual day',
            'cultural fest',
            'debate competition',
            'science fair',
            'art exhibition',
            'literary week',
            'career fair',
            'speech contest',
            'music concert',
            'drama club',
            'dance performance',
            'guest lecture',
            'school trip',
            'seminar',
            'workshop',
            'talent show',
            'cultural event',
            'fun fair',
            'parents day',
            'football match',
            'basketball',
            'athletics',
            'inter-school sports',
            'cricket match',
            'sports day',
            'volleyball',
            'badminton',
            'tennis tournament',
            'swimming competition',
            'table tennis',
            'track and field',
            'marathon',
            'sports trials',
            'chess competition',
            'rugby match',
            'sports training',
            'fitness test',
            'sports camp',
            'sports result',
            'holiday notice',
            'public holiday',
            'school closure',
            'semester break',
            'midterm break',
            'summer vacation',
            'winter holiday',
            'holiday extension',
            'holiday resumption',
            'long weekend',
            'school reopening',
            'independence day',
            'national holiday',
            'holiday schedule',
            'festive break',
            'holiday calendar',
            'official leave',
            'holiday event',
            'school off day',
            'special holiday',
            'fee notice',
            'admission update',
            'staff recruitment',
            'parent meeting',
            'policy update',
            'staff transfer',
            'school board',
            'infrastructure update',
            'budget notice',
            'donation drive',
            'maintenance notice',
            'transport notice',
            'canteen update',
            'school facilities',
            'scholarship',
            'uniform update',
            'security notice',
            'library notice',
            'staff training',
            'management update',
            'weather alert',
            'storm warning',
            'pandemic update',
            'safety notice',
            'fire drill',
            'health emergency',
            'school lockdown',
            'power outage',
            'water shortage',
            'road closure',
            'accident alert',
            'earthquake drill',
            'safety protocol',
            'medical emergency',
            'emergency closure',
            'flood alert',
            'security alert',
            'evacuation',
            'emergency meeting',
            'urgent alert',
            'science club',
            'literature club',
            'music club',
            'drama club',
            'debate club',
            'robotics club',
            'coding club',
            'arts club',
            'dance club',
            'environment club',
            'chess club',
            'photography club',
            'film club',
            'language club',
            'cultural society',
            'history club',
            'mathematics club',
            'volunteer group',
            'student council',
            'youth forum',
        ];
        foreach ($announcementTags as $tag) {
            AnnouncementTag::create([
                'name' => $tag
            ]);
        }
    }
    public function seedSchoolEventTags()
    {
        $schoolEventTags = [
            'school update',
            'policy change',
            'important info',
            'school rules',
            'new policy',
            'daily notice',
            'weekly update',
            'reminder',
            'staff notice',
            'general info',
            'student notice',
            'urgent update',
            'schedule change',
            'parent notice',
            'bulletin',
            'orientation',
            'staff meeting',
            'guidelines',
            'regulations',
            'general alert',
            'curriculum',
            'syllabus update',
            'course info',
            'assignment notice',
            'project work',
            'study materials',
            'class notes',
            'reading list',
            'new subject',
            'curriculum change',
            'lesson plan',
            'tutorials',
            'class update',
            'learning resources',
            'academic calendar',
            'academic deadline',
            'exam prep',
            'study guide',
            'extra class',
            'course announcement',
            'exam timetable',
            'test schedule',
            'midterm exams',
            'final exams',
            'quiz dates',
            'mock exams',
            'exam routine',
            'exam hall allocation',
            'assessment',
            'oral exams',
            'exam guidelines',
            'time table change',
            'exam venue',
            'lab exams',
            'exam deadlines',
            'exam slots',
            'exam notification',
            'exam start date',
            'resit exams',
            'exam plan',
            'exam results',
            'grading system',
            'report card',
            'GPA update',
            'marks release',
            'assessment results',
            'rank list',
            'result sheet',
            'result publication',
            'results deadline',
            'grading notice',
            'progress report',
            'transcript',
            'performance update',
            'semester results',
            'exam scores',
            'evaluation',
            'student ranking',
            'grade review',
            'academic results',
            'school event',
            'annual day',
            'cultural fest',
            'debate competition',
            'science fair',
            'art exhibition',
            'literary week',
            'career fair',
            'speech contest',
            'music concert',
            'drama club',
            'dance performance',
            'guest lecture',
            'school trip',
            'seminar',
            'workshop',
            'talent show',
            'cultural event',
            'fun fair',
            'parents day',
            'football match',
            'basketball',
            'athletics',
            'inter-school sports',
            'cricket match',
            'sports day',
            'volleyball',
            'badminton',
            'tennis tournament',
            'swimming competition',
            'table tennis',
            'track and field',
            'marathon',
            'sports trials',
            'chess competition',
            'rugby match',
            'sports training',
            'fitness test',
            'sports camp',
            'sports result',
            'holiday notice',
            'public holiday',
            'school closure',
            'semester break',
            'midterm break',
            'summer vacation',
            'winter holiday',
            'holiday extension',
            'holiday resumption',
            'long weekend',
            'school reopening',
            'independence day',
            'national holiday',
            'holiday schedule',
            'festive break',
            'holiday calendar',
            'official leave',
            'holiday event',
            'school off day',
            'special holiday',
            'fee notice',
            'admission update',
            'staff recruitment',
            'parent meeting',
            'policy update',
            'staff transfer',
            'school board',
            'infrastructure update',
            'budget notice',
            'donation drive',
            'maintenance notice',
            'transport notice',
            'canteen update',
            'school facilities',
            'scholarship',
            'uniform update',
            'security notice',
            'library notice',
            'staff training',
            'management update',
            'weather alert',
            'storm warning',
            'pandemic update',
            'safety notice',
            'fire drill',
            'health emergency',
            'school lockdown',
            'power outage',
            'water shortage',
            'road closure',
            'accident alert',
            'earthquake drill',
            'safety protocol',
            'medical emergency',
            'emergency closure',
            'flood alert',
            'security alert',
            'evacuation',
            'emergency meeting',
            'urgent alert',
            'science club',
            'literature club',
            'music club',
            'drama club',
            'debate club',
            'robotics club',
            'coding club',
            'arts club',
            'dance club',
            'environment club',
            'chess club',
            'photography club',
            'film club',
            'language club',
            'cultural society',
            'history club',
            'mathematics club',
            'volunteer group',
            'student council',
            'youth forum',
        ];
        foreach ($schoolEventTags as $tag) {
            EventTag::create([
                'name' => $tag
            ]);
        }
    }
    public function seedAnnouncementCategories()
    {
        $schoolBranch = Schoolbranches::first();
        $announcementCategories = [
            [
                'name' => 'General News',
                'description' => 'Announcements covering broad organizational updates significant changes or information relevant to all stakeholders',
            ],
            [
                'name' => 'Product Service Updates',
                'description' => 'Details regarding new features enhancements bug fixes or sunsetting of specific products or services',
            ],
            [
                'name' => 'Event Information',
                'description' => 'Notices about upcoming events webinars workshops conferences or important dates and deadlines',
            ],
            [
                'name' => 'System Maintenance Outages',
                'description' => 'Scheduled notifications for planned system downtime maintenance windows or reports on unexpected service interruptions and resolutions',
            ],
            [
                'name' => 'Policy Changes',
                'description' => 'Formal communications concerning updates revisions or additions to company policies terms of service or compliance requirements',
            ],
            [
                'name' => 'Security Alerts',
                'description' => 'Urgent information about potential security vulnerabilities necessary actions for users or updates on security protocols',
            ],
            [
                'name' => 'Hiring Careers',
                'description' => 'Announcements related to job openings recruitment drives internal promotions or changes within the Human Resources department',
            ],
            [
                'name' => 'Financial Reports',
                'description' => 'Public disclosures or internal reports on quarterly earnings financial performance budget updates or investment news',
            ],
            [
                'name' => 'Company Culture Recognition',
                'description' => 'Celebrations of employee achievements birthdays work anniversaries company values or corporate social responsibility initiatives',
            ],
            [
                'name' => 'Training Development',
                'description' => 'Information on new required training sessions available professional development courses or internal knowledge sharing sessions',
            ],
            [
                'name' => 'Emergency Disaster Info',
                'description' => 'Critical and time sensitive instructions or updates related to immediate safety weather emergencies or building closures',
            ],
            [
                'name' => 'Regulatory Compliance',
                'description' => 'Specific announcements regarding new laws industry regulations or external compliance mandates affecting operations',
            ],
        ];
        foreach ($announcementCategories as $category) {
            AnnouncementCategory::create([
                "name" => $category['name'],
                "description" => $category["description"],
                "school_branch_id" => $schoolBranch->id
            ]);
        }
    }
    public function seedEventCategories()
    {
        $schoolBranch = Schoolbranches::first();
        $schoolEventCategories = [
            [
                'name' => 'Academic Calendar',
                'description' => 'Key dates like holidays parent teacher conferences exam weeks and progress report deadlines',
            ],
            [
                'name' => 'Sports Athletics',
                'description' => 'Schedules for team practices games meets tournaments and tryouts for all school sports',
            ],
            [
                'name' => 'Arts Performances',
                'description' => 'Dates for theater productions music concerts art exhibits and dance recitals',
            ],
            [
                'name' => 'Student Life Clubs',
                'description' => 'Meetings and activities for student clubs organizations and extracurricular groups',
            ],
            [
                'name' => 'Fundraising Community',
                'description' => 'Events like school fairs charity drives auctions and community service activities',
            ],
            [
                'name' => 'Grade Level Milestones',
                'description' => 'Specific events for classes like graduation ceremonies field trips and orientation days',
            ],
            [
                'name' => 'Information Sessions',
                'description' => 'Meetings for parents and students regarding college planning course selection or school policy changes',
            ],
            [
                'name' => 'Testing Assessments',
                'description' => 'Dates for standardized tests like SAT ACT state assessments and in-class finals',
            ],
            [
                'name' => 'Professional Development',
                'description' => 'Training workshops or staff-only meetings for teacher and administrator growth',
            ],
            [
                'name' => 'Health Wellness',
                'description' => 'Events like school vaccination clinics fitness days mental health awareness workshops or safety drills',
            ],
            [
                'name' => 'School Board Meetings',
                'description' => 'Public notices of dates times and agendas for official school district board meetings',
            ],
            [
                'name' => 'Alumni Events',
                'description' => 'Events specifically for former students like class reunions homecoming gatherings or networking mixers',
            ],
        ];


        foreach ($schoolEventCategories as $category) {
            EventCategory::create([
                "name" => $category['name'],
                "description" => $category["description"],
                "school_branch_id" => $schoolBranch->id
            ]);
        }
    }
    public function seedAdditionalFeeCategories()
    {
        $schoolBranch = Schoolbranches::first();

        $studentAdditionalFees = [
            // 1. Administrative and Enrollment Fees
            [
                'name' => 'Enrollment Registration Fee',
                'description' => 'A one-time or annual non-refundable fee to cover administrative costs of processing student admission and registration',
            ],
            [
                'name' => 'Application Fee',
                'description' => 'A fee required to submit an application for admission often paid only once',
            ],
            [
                'name' => 'Technology Fee',
                'description' => 'A charge to fund campus-wide technology infrastructure IT support and student access to computer labs and Wi-Fi services',
            ],
            [
                'name' => 'ID Card Fee',
                'description' => 'A fee for the initial issue or replacement of a student identification card',
            ],
            // 2. Service and Activity Fees (Mandatory Campus Services)
            [
                'name' => 'Student Activity Fee',
                'description' => 'A mandatory charge to fund extracurricular activities student organizations and campus events',
            ],
            [
                'name' => 'Health Service Fee',
                'description' => 'A mandatory fee to support the operations of the campus health clinic and basic student health services',
            ],
            [
                'name' => 'Transportation Parking Fee',
                'description' => 'A fee for students wishing to use campus parking facilities or access to campus shuttle and public transport services',
            ],
            [
                'name' => 'Library Fee',
                'description' => 'A fee to support library resources maintenance and access to digital research databases',
            ],
            [
                'name' => 'Building Facility Fee',
                'description' => 'A charge to cover the maintenance upkeep and future expansion of campus buildings and facilities',
            ],
            // 3. Course and Material Fees
            [
                'name' => 'Lab Course Fees',
                'description' => 'Specific charges for courses that require consumables materials or specialized equipment such as science labs art studios or engineering workshops',
            ],
            [
                'name' => 'Books Supplies Fee',
                'description' => 'Charges for required textbooks course packets or specific academic supplies often provided directly by the institution',
            ],
            [
                'name' => 'Examination Graduation Fee',
                'description' => 'Fees related to taking standardized exams final board exams or administrative costs associated with graduation application and diploma',
            ],
            // 4. Living and Housing Fees (For on-campus students)
            [
                'name' => 'Room Board Fee',
                'description' => 'The cost for on-campus housing (room) and mandatory meal plan (board)',
            ],
            [
                'name' => 'Orientation Fee',
                'description' => 'A fee for mandatory new student orientation programs including materials activities and temporary housing if applicable',
            ],
        ];

        foreach ($studentAdditionalFees as $category) {
            AdditionalFeesCategory::create([
                "title" => $category['name'],
                "description" => $category["description"],
                "school_branch_id" => $schoolBranch->id
            ]);
        }
    }
    public function seedSchoolExpensesCategories()
    {
        $schoolBranch = Schoolbranches::first();
        $schoolExpensesCategories = [
            [
                'name' => 'Tuition Fees',
                'description' => 'The main cost for academic instruction, calculated per credit hour per semester or as a flat rate',
            ],
            [
                'name' => 'Mandatory Fees',
                'description' => 'Required charges for campus services like student activities technology health services and facility maintenance',
            ],
            [
                'name' => 'Room and Board',
                'description' => 'The cost for on-campus housing and the mandatory meal plan',
            ],
            [
                'name' => 'Course Specific Fees',
                'description' => 'Additional charges for specific classes that require special materials equipment or lab time (e.g., science art or nursing classes)',
            ],
            [
                'name' => 'Books and Supplies',
                'description' => 'Estimated costs for required textbooks digital materials notebooks and other academic supplies',
            ],
            [
                'name' => 'Personal Expenses',
                'description' => 'Estimated costs for clothing laundry personal care items entertainment and other miscellaneous needs',
            ],
            [
                'name' => 'Transportation Costs',
                'description' => 'Estimated costs for travel to and from campus including daily commuting expenses or travel home during breaks',
            ],
            [
                'name' => 'Health Insurance',
                'description' => 'The cost for a required student health insurance plan if not covered by a private or family plan',
            ],
            [
                'name' => 'Loan Fees',
                'description' => 'Estimated fees charged by lenders for processing federal or private student loans',
            ],
        ];

        foreach ($schoolExpensesCategories as $category) {
            Schoolexpensescategory::create([
                "name" => $category['name'],
                "description" => $category["description"],
                "school_branch_id" => $schoolBranch->id
            ]);
        }
    }
    public function teacherSpecialtyPreferenceSeeder()
    {
        $schoolBranch = SchoolBranches::first();
        $teachers = Teacher::all();
        $specialties = Specialty::all();

        $specialtyIds = $specialties->pluck('id')->toArray();
        $schoolBranchId = $schoolBranch->id;

        foreach ($teachers as $teacher) {
            $numberOfSpecialties = rand(3, 5);

            $selectedSpecialtyIds = (array) array_rand(array_flip($specialtyIds), $numberOfSpecialties);

            if ($numberOfSpecialties > count($specialtyIds)) {
                $selectedSpecialtyIds = $specialtyIds;
            }

            foreach ($selectedSpecialtyIds as $specialtyId) {
                TeacherSpecailtyPreference::create([
                    'school_branch_id' => $schoolBranchId,
                    'teacher_id'       => $teacher->id,
                    'specialty_id'     => $specialtyId,
                ]);
            }
        }
    }
    public function schoolHallSeeder()
    {
        $schoolBranch = SchoolBranches::first();
        if (!$schoolBranch) return;

        $lectureNames = [
            'Main Auditorium',
            'Grand Lecture Hall',
            'Amphitheatre',
            'Hall A-{num}',
            'Hall B-{num}',
            'Hall C-{num}',
            'Hall D-{num}',
            'Hall E-{num}',
            'Room {num}',
            'Lecture Room {num}',
            'Classroom {num}',
            'Seminar Room {num}',
            'Conference Room {num}',
            'Mini Auditorium {num}'
        ];

        $labNames = [
            'Computer Lab {num}',
            'Programming Lab {num}',
            'Software Lab {num}',
            'Physics Lab {num}',
            'Chemistry Lab {num}',
            'Biology Lab {num}',
            'Electronics Lab {num}',
            'Microbiology Lab',
            'Biochemistry Lab',
            'Network Lab {num}',
            'Hardware Lab {num}',
            'Robotics Lab',
            'Language Lab {num}',
            'Media Lab',
            'Design Studio {num}',
            'Science Lab {num}',
            'Research Lab {num}',
            'Advanced Lab {num}'
        ];

        $locations = [
            'Building A',
            'Building B',
            'Building C',
            'Building D',
            'Building E',
            'Main Building',
            'Science Block',
            'Engineering Block',
            'IT Block',
            'Annex Building',
            'West Wing',
            'East Wing',
            'North Block',
            'South Block'
        ];

        $halls = [];

        for ($i = 1; $i <= 100; $i++) {
            $template = $lectureNames[array_rand($lectureNames)];
            $name = str_replace('{num}', $i < 10 ? '0' . $i : $i, $template);
            if (str_contains($name, '{num}')) {
                $name = $template . ' ' . ($i < 10 ? '0' . $i : $i);
            }

            $halls[] = [
                'name'         => $name,
                'capacity'     => rand(30, 40) === 1 ? rand(200, 400) : rand(40, 150),
                'type'         => 'lecture',
                'is_exam_hall' => rand(0, 100) <= 70,
            ];
        }

        for ($i = 1; $i <= 35; $i++) {
            $template = $labNames[array_rand($labNames)];
            $name = str_replace('{num}', $i < 10 ? '0' . $i : $i, $template);
            if (str_contains($name, '{num}')) {
                $name = $template . ' ' . ($i < 10 ? '0' . $i : $i);
            }

            $halls[] = [
                'name'         => $name,
                'capacity'     => rand(20, 40),
                'type'         => 'lab',
                'is_exam_hall' => false,
            ];
        }

        shuffle($halls);

        foreach ($halls as $hall) {
            Hall::create([
                'name'             => $hall['name'],
                'capacity'         => $hall['capacity'],
                'status'           => 'available',
                'type'             => $hall['type'],
                'location'         => $locations[array_rand($locations)],
                'school_branch_id' => $schoolBranch->id,
                'is_exam_hall'     => $hall['is_exam_hall'],
            ]);
        }
    }
    public function specialtyHallSeeder()
    {
        $schoolBranch = SchoolBranches::first();
        if (!$schoolBranch) return;

        $specialties = Specialty::all();
        $halls       = Hall::all();

        if ($specialties->isEmpty() || $halls->isEmpty()) return;

        foreach ($specialties as $specialty) {
            $hallCount = rand(2, 6);
            $randomHalls = $halls->random(min($hallCount, $halls->count()));

            foreach ($randomHalls as $hall) {
                SpecialtyHall::create([
                    'specialty_id'     => $specialty->id,
                    'level_id'         => $specialty->level_id,
                    'hall_id'          => $hall->id,
                    'school_branch_id' => $schoolBranch->id,
                ]);
            }
        }
    }
    public function teacherCoursePreference()
    {
        $schoolBranch = SchoolBranches::first();
        if (!$schoolBranch) return;

        $courses = Courses::where('school_branch_id', $schoolBranch->id)
            ->with(['specialty', 'level'])
            ->get();

        $teacherPreferences = TeacherSpecailtyPreference::where('school_branch_id', $schoolBranch->id)
            ->with('teacher')
            ->get();

        if ($courses->isEmpty() || $teacherPreferences->isEmpty()) return;

        foreach ($teacherPreferences as $preference) {
            $teacher = $preference->teacher;
            $preferredSpecialtyId = $preference->specialty_id;

            $matchingCourses = $courses->where('specialty_id', $preferredSpecialtyId);

            if ($matchingCourses->isEmpty()) continue;

            $preferredCount = rand(4, 10);
            $selectedCourses = $matchingCourses->random(min($preferredCount, $matchingCourses->count()));

            foreach ($selectedCourses as $course) {
                TeacherCoursePreference::create([
                    'teacher_id'       => $teacher->id,
                    'course_id'        => $course->id,
                    'school_branch_id' => $schoolBranch->id,
                ]);
            }
        }
    }
    public function timetableSeeder()
    {
        $schoolBranch = SchoolBranches::first();
        if (!$schoolBranch) {
            Log::warning('Timetable Seeder: No SchoolBranch found. Exiting seeder.');
            return;
        }

        $schoolSemesters = SchoolSemester::where('school_branch_id', $schoolBranch->id)
            ->with(['semester', 'specialty'])
            ->get();

        if ($schoolSemesters->isEmpty()) {
            Log::warning('Timetable Seeder: No SchoolSemesters found for branch ID ' . $schoolBranch->id . '. Exiting seeder.');
            return;
        }

        $teacherPrefs = TeacherCoursePreference::where('school_branch_id', $schoolBranch->id)
            ->with(['teacher', 'course'])
            ->get()
            ->groupBy('course_id');


        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $timeSlots = [
            '07:30',
            '08:00',
            '08:30',
            '09:00',
            '09:30',
            '10:00',
            '10:30',
            '11:00',
            '11:30',
            '12:30',
            '13:00',
            '13:30',
            '14:00',
            '14:30',
            '15:00',
            '15:30',
            '16:00',
            '16:30',
            '17:00',
            '17:30',
            '18:00'
        ];

        $formatDuration = function ($minutes) {
            $hours = floor($minutes / 60);
            $mins  = $minutes % 60;
            if ($hours > 0 && $mins > 0) {
                return "{$hours}h {$mins}min";
            } elseif ($hours > 0) {
                return "{$hours}h";
            } else {
                return "{$mins}min";
            }
        };

        foreach ($schoolSemesters as $schoolSemester) {
            $specialtyId = $schoolSemester->specialty_id;
            $semesterId  = $schoolSemester->semester_id;
            $schoolSemesterId = $schoolSemester->id;
            $levelId     = $schoolSemester->specialty->level_id;
            $studentBatchId = $schoolSemester->student_batch_id;
            Log::info('Processing SchoolSemester: Specialty ID ' . $specialtyId . ', Semester ID ' . $semesterId . ', Level ID ' . $levelId);

            if (!$levelId) {
                Log::warning('Timetable Seeder: Skipping SchoolSemester ' . $schoolSemester->id . ' because Level ID is null.');
                continue;
            }

            $courses = Courses::where('school_branch_id', $schoolBranch->id)
                ->where('semester_id', $semesterId)
                ->where('specialty_id', $specialtyId)
                ->inRandomOrder()
                ->get();

            $availableHalls = SpecialtyHall::where('specialty_id', $specialtyId)
                ->where('level_id', $levelId)
                ->inRandomOrder()
                ->pluck('hall_id');


            if (!$studentBatchId || $availableHalls->isEmpty() || $courses->isEmpty()) {
                Log::warning('Timetable Seeder: Skipping Specialty/Level combination. StudentBatch ID: ' . (int) $studentBatchId .
                    ', Halls found: ' . $availableHalls->count() .
                    ', Courses found: ' . $courses->count());
                continue;
            }

            $totalEntries = rand(120, 150);
            $daysWithBreak = [];

            for ($i = 0; $i < $totalEntries; $i++) {
                $day = $days[array_rand($days)];

                if (!in_array($day, $daysWithBreak)) {
                    try {
                        Timetable::create([
                            'school_branch_id'  => $schoolBranch->id,
                            'specialty_id'      => $specialtyId,
                            'level_id'          => $levelId,
                            'course_id'         => null,
                            'teacher_id'        => null,
                            'day_of_week'       => $day,
                            'start_time'        => '12:00',
                            'end_time'          => '12:30',
                            'duration'          => '30min',
                            'semester_id'       => $schoolSemesterId,
                            'break'             => true,
                            'hall_id'           => null,
                            'student_batch_id'  => $studentBatchId,
                        ]);
                        $daysWithBreak[] = $day;
                        Log::debug('Timetable Seeder: Successfully created Break entry for ' . $day . '.');
                    } catch (\Exception $e) {
                        Log::error('Timetable Seeder: FAILED to create Break for ' . $day . '. Error: ' . $e->getMessage());
                    }
                }

                $startTime = $timeSlots[array_rand($timeSlots)];

                try {
                    $start = Carbon::createFromFormat('H:i', $startTime);
                } catch (\Exception $e) {
                    Log::error('Timetable Seeder: FAILED to create Carbon instance for start time: ' . $startTime . '. Error: ' . $e->getMessage());
                    continue;
                }

                if ($startTime >= '12:00' && $startTime < '12:30') {
                    Log::debug('Timetable Seeder: Skipping iteration ' . $i . '. Start time ' . $startTime . ' is during break.');
                    continue;
                }

                $slots   = rand(1, 4);
                $minutes = $slots * 30;
                $end     = $start->copy()->addMinutes($minutes);
                $endTime = $end->format('H:i');

                if ($endTime > '18:00') {
                    Log::debug('Timetable Seeder: Skipping iteration ' . $i . '. End time ' . $endTime . ' is after 18:00.');
                    continue;
                }

                $classStart = $start->format('H:i');
                $classEnd   = $end->format('H:i');

                if (
                    ($classStart >= '12:00' && $classStart < '12:30') ||
                    ($classEnd > '12:00' && $classEnd <= '12:30') ||
                    ($classStart < '12:00' && $classEnd > '12:30')
                ) {
                    Log::debug('Timetable Seeder: Skipping iteration ' . $i . '. Time slot ' . $classStart . '-' . $classEnd . ' conflicts with break time (12:00-12:30).');
                    continue;
                }

                $course = $courses->random();
                $prefs  = $teacherPrefs->get($course->id);

                if (!$prefs || $prefs->isEmpty()) {
                    Log::debug('Timetable Seeder: Skipping iteration ' . $i . '. No teacher preference found for Course ID ' . $course->id . '.');
                    continue;
                }

                $teacher = $prefs->random()->teacher;

                try {
                    Timetable::create([
                        'school_branch_id'  => $schoolBranch->id,
                        'specialty_id'      => $specialtyId,
                        'level_id'          => $levelId,
                        'course_id'         => $course->id,
                        'teacher_id'        => $teacher->id,
                        'day_of_week'       => $day,
                        'start_time'        => $startTime,
                        'end_time'          => $endTime,
                        'duration'          => $formatDuration($minutes),
                        'semester_id'       => $schoolSemesterId,
                        'break'             => false,
                        'hall_id'           => $availableHalls->random(),
                        'student_batch_id'  => $studentBatchId,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Timetable Seeder: FAILED to create CLASS entry for Specialty ' . $specialtyId . '/Level ' . $levelId . '. Error: ' . $e->getMessage() .
                        ' | Data: Day ' . $day . ', Start ' . $startTime . ', End ' . $endTime . ', Course ' . $course->id . ', Teacher ' . $teacher->id);
                }
            }
        }
    }
    public function electionType()
    {
        $schoolBranch = SchoolBranches::first();

        ElectionType::create([
            'election_title' => 'Student Government Election',
            'status' => 'active',
            'description' => 'This election selects the core student leadership body responsible for representing students, coordinating student activities, and acting as the main communication bridge between students and school administration.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Class Representative Election',
            'status' => 'active',
            'description' => 'Students vote for representatives who manage class-level concerns, communicate classroom issues, and support teachers in administering academic and behavioral policies.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Club Leadership Election',
            'status' => 'active',
            'description' => 'Clubs and associations elect presidents, secretaries, and coordinators to organize meetings, manage activities, and maintain structure within extracurricular groups.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Prefect Election',
            'status' => 'active',
            'description' => 'A formal process to elect prefects who oversee discipline, support orderliness in school operations, and assist staff in managing daily routines across the campus.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Sports Team Captain Election',
            'status' => 'active',
            'description' => 'Athletes select captains who lead training sessions, encourage team spirit, represent teams during competitions, and collaborate with coaches to maintain discipline and performance standards.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Academic Council Election',
            'status' => 'active',
            'description' => 'Students choose academic delegates responsible for voicing academic concerns, participating in curriculum improvement discussions, and contributing to decisions on academic policy.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Dormitory Leadership Election',
            'status' => 'active',
            'description' => 'Boarding students elect dorm heads and assistant heads who ensure orderliness, support the welfare of residents, and act as liaisons between students and dorm supervisors.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Cultural Committee Election',
            'status' => 'active',
            'description' => 'This election forms the team responsible for planning cultural events, promoting creative expression, and preserving traditions and diversity within the school community.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Health Committee Election',
            'status' => 'active',
            'description' => 'Students are elected to promote health awareness, hygiene practices, and wellness activities, working closely with school health staff to ensure a safe environment.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Environmental Committee Election',
            'status' => 'active',
            'description' => 'This election selects students who lead environmental conservation efforts, organize clean-up activities, promote recycling, and support sustainability initiatives on campus.',
            'school_branch_id' => $schoolBranch->id,
        ]);
    }
    public function electionRoles()
    {
        $faker = Faker::create();
        $schoolBranch = SchoolBranches::first();
        $elections = ElectionType::all();

        foreach ($elections as $election) {
            $rolesCount = rand(10, 15);

            for ($i = 0; $i < $rolesCount; $i++) {
                ElectionRoles::create([
                    'name' => ucfirst($faker->unique()->jobTitle()),
                    'description' => $faker->sentence(20),
                    'election_type_id' => $election->id,
                    'status' => $faker->randomElement(['active', 'inactive']),
                    'school_branch_id' => $schoolBranch->id,
                ]);
            }
        }
    }
}
