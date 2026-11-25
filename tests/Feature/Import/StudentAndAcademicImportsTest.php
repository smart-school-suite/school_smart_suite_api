<?php

namespace Tests\Feature\Import;

use App\Models\Job\SystemJob;
use App\Models\Schooladmin;
use App\Events\Student\StudentRegistered;
use App\Models\Student;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Grade scale, course, joint course, student, guardian and batch imports, end to end through their routes. */
class StudentAndAcademicImportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('r2');
        (new RoleSeeder())->run();

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert([
            'id' => 'admin-1', 'first_name' => 'Fon', 'last_name' => 'Nathalie', 'name' => 'Fon Nathalie',
            'email' => 'fon@example.com', 'password' => 'x', 'username' => 'fnathalie', 'school_branch_id' => 'branch-1',
        ]);
        DB::table('genders')->insert([['id' => 'g-m', 'name' => 'Male'], ['id' => 'g-f', 'name' => 'Female']]);
        foreach (['lvl-1' => ['Level One', 200], 'lvl-2' => ['Level Two', 300]] as $id => [$name, $level]) {
            DB::table('levels')->insert(['id' => $id, 'name' => $name, 'level' => $level, 'program_name' => strtolower(str_replace(' ', '_', $name)), 'level_type_id' => 't']);
        }
        DB::table('departments')->insert(['id' => 'd-1', 'department_name' => 'Computing', 'school_branch_id' => 'branch-1']);
        foreach (['swe-1' => ['Software Engineering', 'lvl-1'], 'net-1' => ['Networking', 'lvl-1'], 'swe-2' => ['Software Engineering', 'lvl-2']] as $id => [$name, $level]) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd-1', 'level_id' => $level]);
        }

        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');
    }

    private function import(string $url, string $csv, array $standardFields, array $repeatableGroups = []): SystemJob
    {
        $response = $this->post($url, [
            'file' => UploadedFile::fake()->createWithContent('import.csv', $csv),
            'mapping' => ['standardFields' => $standardFields, 'repeatableGroups' => $repeatableGroups],
        ], ['Accept' => 'application/json'])->assertStatus(202);

        return SystemJob::findOrFail($response->json('data.id'));
    }

    private function same(array $columns): array
    {
        return array_combine($columns, $columns);
    }

    private function summaries(SystemJob $job): array
    {
        return collect($job->result['error_groups'] ?? [])->pluck('summary')->all();
    }

    public function test_student_batch_import(): void
    {
        DB::table('student_batches')->insert(['id' => 'b-0', 'name' => '2024/2025', 'description' => 'x', 'school_branch_id' => 'branch-1']);

        $job = $this->import('/api/v1/student-batch/import', "name,description\n2025/2026,New intake\n2024/2025,Old\n2026/2027,\n", $this->same(['name', 'description']));

        $this->assertSame('Student Batch', $job->category->name);
        $this->assertSame(1, $job->successful_items);
        $this->assertEqualsCanonicalizing([
            'Row 3: A student batch with this name already exists at this school.',
            'Row 4: The description field is required.',
        ], $this->summaries($job));
    }

    public function test_guardian_import(): void
    {
        $csv = "name,phone,address,contact_method,language\n"
            . "Paul Biya,677 12 34 56,Yaounde,SMS,french\n"   // 2
            . "Chantal Biya,699000000,Yaounde,,\n"            // 3: defaults
            . "Someone,677123456,Douala,call,English\n"       // 4: same phone as row 2
            . "Other,670000000,Douala,pigeon,English\n";      // 5: bad contact method

        $job = $this->import('/api/v1/parent/import', $csv, $this->same(['name', 'phone', 'address', 'contact_method', 'language']));

        $this->assertSame('Guardian Import', $this->getJson("/api/v1/system-jobs/{$job->id}")->json('data.title'));
        $this->assertSame(2, $job->successful_items);
        $paul = DB::table('parents')->where('name', 'Paul Biya')->first();
        $this->assertSame(['text message', 'French'], [$paul->preferred_contact_method, $paul->preferred_language]);
        $this->assertSame('phone call', DB::table('parents')->where('name', 'Chantal Biya')->value('preferred_contact_method'));
        $this->assertEqualsCanonicalizing([
            'Row 4: Repeats a row higher up in the file; only the first one was imported.',
            'Row 5: Contact method must be phone call or text message.',
        ], $this->summaries($job));
    }

    public function test_student_import(): void
    {
        Event::fake([StudentRegistered::class]);
        DB::table('student_batches')->insert(['id' => 'b-1', 'name' => '2025/2026', 'description' => 'x', 'school_branch_id' => 'branch-1']);
        DB::table('parents')->insert(['id' => 'p-1', 'name' => 'Paul Biya', 'phone' => '677123456', 'address' => 'Yaounde', 'school_branch_id' => 'branch-1']);
        DB::table('stu_par_relationships')->insert(['id' => 'r-1', 'name' => 'Father']);
        DB::table('student_sources')->insert(['id' => 'src-1', 'name' => 'Social Media', 'description' => 'x']);
        DB::table('students')->insert([
            'id' => 's-0', 'name' => 'Existing Student', 'first_name' => 'Existing', 'last_name' => 'Student', 'email' => 'taken@example.com',
            'password' => 'x', 'username' => 'estudent', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'guardian_id' => 'p-1',
            'student_batch_id' => 'b-1', 'relationship_id' => 'r-1', 'student_source_id' => 'src-1', 'gender_id' => 'g-m',
        ]);

        $header = "email,first_name,last_name,gender,phone,specialty,level,batch,guardian,relationship,student_source,fee_payment_format\n";
        $csv = $header
            . "ada@example.com,Ada,Lovelace,F,690000001,Software Engineering,Level One,2025/2026,Paul Biya,father,social media,one time\n" // 2
            . "alan@example.com,Alan,Turing,Male,,Networking,,2025/2026,677 123 456,Father,Social Media,\n"                              // 3: guardian by phone
            . "taken@example.com,New,Name,Male,,Networking,,2025/2026,Paul Biya,Father,Social Media,\n"                                // 4
            . "x@example.com,No,Batch,Male,,Networking,,2030/2031,Paul Biya,Father,Social Media,\n"                                   // 5
            . "y@example.com,No,Guardian,Male,,Networking,,2025/2026,Nobody,Father,Social Media,\n"                                   // 6
            . "z@example.com,Bad,Relation,Male,,Networking,,2025/2026,Paul Biya,Cousin,Social Media,\n";                              // 7

        $job = $this->import('/api/v1/student/import', $csv, $this->same([
            'email', 'first_name', 'last_name', 'gender', 'phone', 'specialty', 'level', 'batch', 'guardian', 'relationship', 'student_source', 'fee_payment_format',
        ]));

        $ada = Student::where('email', 'ada@example.com')->firstOrFail();
        $this->assertSame(['Ada Lovelace', 'g-f', 'swe-1', 'p-1', 'one time', 'alovelace'], [$ada->name, $ada->gender_id, $ada->specialty_id, $ada->guardian_id, $ada->payment_format, $ada->username]);
        $this->assertSame('p-1', Student::where('email', 'alan@example.com')->value('guardian_id'));
        $this->assertSame(2, $job->successful_items);
        $this->assertEqualsCanonicalizing([
            'Row 4: A student with this email already exists at this school.',
            'Row 5: No student batch with this name at your school. Create it first, or import student batches before students.',
            'Row 6: No guardian with this name or phone number at your school. Import guardians before students.',
            'Row 7: Relationship not recognised. Use Father.',
        ], $this->summaries($job));
    }

    private function courseReferences(): void
    {
        DB::table('semesters')->insert([
            ['id' => 'sem-1', 'name' => 'first semester', 'count' => 1, 'program_name' => 'FIS'],
            ['id' => 'sem-2', 'name' => 'second semester', 'count' => 2, 'program_name' => 'SES'],
        ]);
        DB::table('course_types')->insert([
            ['id' => 'ct-1', 'name' => 'Practical', 'key' => 'practical.course'],
            ['id' => 'ct-2', 'name' => 'Theoritical', 'key' => 'theoritical.course'],
        ]);
    }

    public function test_course_import(): void
    {
        $this->courseReferences();
        DB::table('courses')->insert(['id' => 'c-0', 'course_code' => 'SWE100', 'course_title' => 'Old', 'credit' => 3, 'school_branch_id' => 'branch-1', 'semester_id' => 'sem-1']);

        $csv = "course_code,course_title,course_credit,semester,specialty,level,type_1,type_2\n"
            . "SWE101,Programming,4,First Semester,Software Engineering,Level One,Practical,theoretical\n" // 2
            . "SWE102,Databases,3,2,Software Engineering,Level Two,Practical|Theoritical,\n"             // 3
            . "swe100,Again,3,1,Networking,,Practical,\n"                                                // 4: code exists
            . "NET101,Routing,12,1,Networking,,Practical,\n"                                             // 5: credit
            . "NET102,Switching,3,Ninth,Networking,,Practical,\n"                                        // 6: semester
            . "NET103,Wireless,3,1,Networking,,,\n";                                                     // 7: no type

        $job = $this->import('/api/v1/course/import', $csv,
            $this->same(['course_code', 'course_title', 'course_credit', 'semester', 'specialty', 'level']),
            ['course_types' => [['course_type' => 'type_1'], ['course_type' => 'type_2']]]);

        $course = DB::table('courses')->where('course_code', 'SWE101')->first();
        $this->assertSame(['sem-1', 4], [$course->semester_id, (int) $course->credit]);
        $this->assertSame(2, DB::table('school_course_types')->where('course_id', $course->id)->count());
        $this->assertTrue(DB::table('course_specialties')->where(['course_id' => $course->id, 'specialty_id' => 'swe-1'])->exists());
        $this->assertSame('sem-2', DB::table('courses')->where('course_code', 'SWE102')->value('semester_id'));
        $this->assertEqualsCanonicalizing([
            'Row 4: A course with this code already exists at this school.',
            'Row 5: The course credit field must not be greater than 10.',
            'Row 6: Semester not found. Use a semester name such as First Semester, or its number (1, 2...).',
            'Row 7: At least one course type is required (for example Practical or Theoritical).',
        ], $this->summaries($job));
    }

    public function test_joint_course_import(): void
    {
        $this->courseReferences();

        $csv = "course_code,course_title,course_credit,semester,type_1,specialty_1,level_1,specialty_2,level_2\n"
            . "GEN101,English,2,1,Theoritical,Software Engineering,Level One,Networking,Level One\n" // 2
            . "GEN102,French,2,1,Theoritical,Networking,,,\n"                                       // 3: one specialty
            . "GEN103,Maths,2,1,Theoritical,Networking,,Biology,\n";                                // 4: unknown specialty

        $job = $this->import('/api/v1/joint-course/import', $csv,
            $this->same(['course_code', 'course_title', 'course_credit', 'semester']),
            [
                'course_types' => [['course_type' => 'type_1']],
                'specialties' => [['specialty' => 'specialty_1', 'level' => 'level_1'], ['specialty' => 'specialty_2', 'level' => 'level_2']],
            ]);

        $course = DB::table('courses')->where('course_code', 'GEN101')->first();
        $this->assertEqualsCanonicalizing(['swe-1', 'net-1'], DB::table('course_specialties')->where('course_id', $course->id)->pluck('specialty_id')->all());
        $this->assertSame('Course', $job->category->name);
        $this->assertEqualsCanonicalizing([
            'Row 3: A joint course needs at least two different specialties.',
            'Row 4: No specialty with this name at your school.',
        ], $this->summaries($job));
    }

    public function test_grade_scale_import(): void
    {
        DB::table('grade_scale_categories')->insert([
            ['id' => 'sys-1', 'title' => 'Level One Exam', 'exam_type' => 'exam'],
            ['id' => 'sys-2', 'title' => 'Level One CA', 'exam_type' => 'ca'],
        ]);
        DB::table('school_grade_scale_categories')->insert([
            ['id' => 'cat-1', 'school_branch_id' => 'branch-1', 'grades_category_id' => 'sys-1'],
            ['id' => 'cat-2', 'school_branch_id' => 'branch-1', 'grades_category_id' => 'sys-2'],
        ]);
        foreach (['A', 'B', 'C', 'F'] as $letter) {
            DB::table('letter_grades')->insert(['id' => "lg-{$letter}", 'letter_grade' => $letter]);
        }

        $csv = "grade_title,exam_type,grade_max_score,grade,min_score,max_score,grade_points,result,resit_result,performance\n"
            . "Level One Exam,exam,100,A,80,100,4,passed,no resit,Excellent\n"   // 2
            . "Level One Exam,exam,100,B,60,79.99,3,Pass,No Resit,Good\n"        // 3
            . "Level One Exam,exam,100,C,70,75,2,passed,no resit,Fair\n"         // 4: overlaps B
            . "Level One Exam,exam,20,F,0,9,0,failed,resit,Poor\n"               // 5: other max score
            . "Level One Exam,exam,100,A,0,10,0,failed,resit,Poor\n"             // 6: A twice
            . "Level One CA,,30,A,25,31,4,passed,no resit,Excellent\n"           // 7: above max
            . "Level One CA,,30,B,15,24.99,3,passed,maybe,Good\n";               // 8: resit result

        $job = $this->import('/api/v1/grade-scale/import', $csv, $this->same([
            'grade_title', 'exam_type', 'grade_max_score', 'grade', 'min_score', 'max_score', 'grade_points', 'result', 'resit_result', 'performance',
        ]));

        $this->assertSame(2, $job->successful_items);
        $this->assertSame(100.0, (float) DB::table('school_grade_scale_categories')->where('id', 'cat-1')->value('max_score'));
        $this->assertSame('passed', DB::table('grade_scales')->where('letter_grade_id', 'lg-B')->value('result'));
        $this->assertEquals(79.99, DB::table('grade_scales')->where('letter_grade_id', 'lg-B')->value('maximum_score'), 'Decimals read from the file keep their value.');
        $this->assertEqualsCanonicalizing([
            'Row 4: The score range overlaps another grade of this grade scale. Ranges must not overlap.',
            'Row 5: The grade max score differs from the one of this grade scale. Every grade of a scale must use the same max score.',
            'Row 6: Repeats a row higher up in the file; only the first one was imported.',
            'Row 7: The max score cannot be higher than the grade max score.',
            'Row 8: Resit result must be no resit, resit, high resit potential or low resit potential.',
        ], $this->summaries($job));
    }
}
