<?php

namespace Tests\Feature\Import;

use App\Jobs\AuthenticationJobs\SendPasswordVaiMailJob;
use App\Models\Job\SystemJob;
use App\Models\Schooladmin;
use App\Models\Teacher;
use App\Services\Import\StalledImportJobService;
use App\Services\Job\JobService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The imports of the Administrators module, run end to end: upload through
 * the route, the job runs on the sync queue, and the system job holds the
 * outcome with problems grouped by reason.
 */
class AdministratorImportsTest extends TestCase
{
    use RefreshDatabase;

    private Schooladmin $admin;

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
        DB::table('qualifications')->insert(['id' => 'q-1', 'name' => 'Master of Science', 'abbreviation' => 'MSc', 'level' => 'masters', 'status' => 'active']);

        $this->admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($this->admin);
        $this->actingAs($this->admin, 'schooladmin');
    }

    private function upload(string $url, string $csv, array $standardFields, array $repeatableGroups = [])
    {
        return $this->post($url, [
            'file' => UploadedFile::fake()->createWithContent('import.csv', $csv),
            'mapping' => ['standardFields' => $standardFields, 'repeatableGroups' => $repeatableGroups],
        ], ['Accept' => 'application/json']);
    }

    private function job(string $id): SystemJob
    {
        return SystemJob::findOrFail($id);
    }

    private function teacher(string $name, string $email, array $levels = ['lvl-1']): Teacher
    {
        $teacher = Teacher::create([
            'name' => $name, 'first_name' => explode(' ', $name)[0], 'last_name' => explode(' ', $name)[1] ?? 'X',
            'email' => $email, 'username' => strtolower(str_replace(' ', '', $name)), 'password' => 'x',
            'gender_id' => 'g-m', 'school_branch_id' => 'branch-1',
        ]);
        $teacher->levels()->sync(collect($levels)->mapWithKeys(fn($id) => [$id => ['school_branch_id' => 'branch-1']])->all());

        return $teacher;
    }

    private function teacherMapping(): array
    {
        return [
            ['email' => 'email', 'first_name' => 'first_name', 'last_name' => 'last_name', 'gender' => 'gender', 'phone' => 'phone'],
            [
                'qualifications' => [['qualification' => 'qualification_1', 'field_of_study' => 'field_of_study_1', 'university' => 'university_1', 'year_obtained' => 'year_obtained_1']],
                'allowed_levels' => [['allowed_level' => 'allowed_level_1']],
            ],
        ];
    }

    public function test_teacher_import_creates_teachers_and_groups_rows_failing_for_the_same_reason(): void
    {
        foreach (range(1, 20) as $i) {
            $this->teacher("Existing Teacher{$i}", "existing{$i}@example.com");
        }

        $header = "email,first_name,last_name,gender,phone,qualification_1,field_of_study_1,university_1,year_obtained_1,allowed_level_1\n";
        $rows = "new1@example.com,Ada,Lovelace,Female,677123456,MSc,Maths,UB,2020,Level One|Level Two\n"
            . ",,,,,,,,,\n"                                                            // blank, ignored
            . "new2@example.com,Alan,Turing,male,677123457,MSc,CS,UB,2019,level one\n";
        foreach (range(1, 20) as $i) {
            $rows .= "existing{$i}@example.com,Someone,Else{$i},Male,,MSc,CS,UB,2019,Level One\n";
        }
        $rows .= "new1@example.com,Ada,Again,Female,,MSc,Maths,UB,2020,Level One\n"   // row 25: duplicate in file
            . "bad@example.com,Bad,Gender,Robot,,MSc,CS,UB,2019,Level One\n"          // row 26
            . "lvl@example.com,No,Level,Male,,MSc,CS,UB,2019,Level Nine\n";            // row 27

        [$standard, $groups] = $this->teacherMapping();
        $response = $this->upload('/api/v1/teacher/import', $header . $rows, $standard, $groups)->assertStatus(202);

        $job = $this->job($response->json('data.id'));
        $this->assertSame('Teacher Import', $response->json('data.title'));
        $this->assertSame(SystemJob::STATUS_COMPLETED_WITH_ISSUES, $job->status);
        $this->assertSame(25, $job->total_items);
        $this->assertSame(2, $job->successful_items);
        $this->assertSame(23, $job->failed_items);
        $this->assertSame(100, $job->progress);
        $this->assertSame('Imported 2 of 25 teachers; 23 rows need attention.', $job->stage);
        $this->assertSame(1, $job->result['blank_rows']);

        $ada = Teacher::where('email', 'new1@example.com')->firstOrFail();
        $this->assertSame('Ada Lovelace', $ada->name);
        $this->assertSame('g-f', $ada->gender_id);
        $this->assertEqualsCanonicalizing(['lvl-1', 'lvl-2'], $ada->levels()->pluck('levels.id')->all());
        $this->assertSame('UB', $ada->qualifications()->first()->pivot->institution);
        $this->assertSame('677123457', Teacher::where('email', 'new2@example.com')->value('phone'));

        $summaries = collect($job->result['error_groups'])->pluck('summary')->all();
        $this->assertSame([
            'Rows 5–24: A teacher with this email already exists at this school.',
            'Row 25: Repeats a row higher up in the file; only the first one was imported.',
            'Row 26: Gender must be Male or Female.',
            "Row 27: Teaching level not found in your school's level system. Use level names such as Level One, Level Two.",
        ], $summaries);
    }

    public function test_a_mapped_column_missing_from_the_file_fails_the_import_and_names_the_column(): void
    {
        [$standard, $groups] = $this->teacherMapping();
        $csv = "email,first_name,last_name,gender\nx@example.com,A,B,Male\n";

        $response = $this->upload('/api/v1/teacher/import', $csv, $standard, $groups)->assertStatus(202);
        $job = $this->job($response->json('data.id'));

        $this->assertSame(SystemJob::STATUS_FAILED, $job->status);
        $this->assertSame('columns_missing', $job->error_code);
        $this->assertStringContainsString('"phone"', $job->error_message);
        $this->assertStringContainsString('"qualification_1"', $job->error_message);
        Storage::disk('r2')->assertDirectoryEmpty('imports/teacher/branch-1');
    }

    public function test_an_unmapped_required_column_is_rejected_before_upload(): void
    {
        $this->upload('/api/v1/teacher-specialty/import', "teacher\nA\n", ['teacher' => 'teacher'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['mapping.standardFields.specialty']);
    }

    public function test_school_admin_import_creates_admins_and_emails_their_password(): void
    {
        Bus::fake([SendPasswordVaiMailJob::class]);

        $csv = "Email,First Name,Last Name,Sex\nNew.Admin@Example.com,New,Admin,F\nfon@example.com,Fon,Nathalie,Female\n";
        $response = $this->upload('/api/v1/school-admin/import', $csv, [
            'email' => 'Email', 'first_name' => 'First Name', 'last_name' => 'Last Name', 'gender' => 'Sex',
        ])->assertStatus(202);

        $job = $this->job($response->json('data.id'));
        $admin = Schooladmin::where('email', 'new.admin@example.com')->firstOrFail();

        $this->assertSame('New Admin', $admin->name);
        $this->assertSame('g-f', $admin->gender_id);
        $this->assertTrue($admin->hasRole('schoolAdmin'));
        $this->assertSame(SystemJob::STATUS_COMPLETED_WITH_ISSUES, $job->status);
        $this->assertSame('School Admin', $job->category->name);
        $this->assertSame(
            ['Row 3: A school admin with this email already exists at this school.'],
            collect($job->result['error_groups'])->pluck('summary')->all()
        );
        Bus::assertDispatchedTimes(SendPasswordVaiMailJob::class, 1);
    }

    private function specialties(): void
    {
        DB::table('departments')->insert(['id' => 'd-1', 'department_name' => 'Computing', 'school_branch_id' => 'branch-1']);
        DB::table('specialties')->insert([
            ['id' => 'swe-1', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd-1', 'level_id' => 'lvl-1'],
            ['id' => 'swe-2', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd-1', 'level_id' => 'lvl-2'],
            ['id' => 'net-1', 'specialty_name' => 'Networking', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd-1', 'level_id' => 'lvl-1'],
        ]);
    }

    public function test_teacher_specialty_import_follows_the_assignment_rules(): void
    {
        $this->specialties();
        $ada = $this->teacher('Ada Lovelace', 'ada@example.com', ['lvl-1']);
        $ada->specialties()->attach('net-1', ['school_branch_id' => 'branch-1']);

        $csv = "teacher,specialty,level\n"
            . "Ada Lovelace,Software Engineering,Level One\n"   // 2: assigned
            . "ada@example.com,Networking,\n"                    // 3: already assigned
            . "Ada Lovelace,Software Engineering,Level Two\n"    // 4: level not allowed
            . "Ada Lovelace,Software Engineering,\n"             // 5: ambiguous
            . "Grace Hopper,Networking,\n";                      // 6: unknown teacher

        $response = $this->upload('/api/v1/teacher-specialty/import', $csv, ['teacher' => 'teacher', 'specialty' => 'specialty', 'level' => 'level'])
            ->assertStatus(202);
        $job = $this->job($response->json('data.id'));

        $this->assertSame(['net-1', 'swe-1'], $ada->specialties()->orderBy('specialties.id')->pluck('specialties.id')->all());
        $this->assertSame(1, $job->successful_items);
        $this->assertEqualsCanonicalizing([
            'Row 3: The teacher is already assigned to this specialty.',
            "Row 4: The teacher is not allowed to teach this specialty's level. Add the level to the teacher's teaching levels first.",
            'Row 5: Several specialties have this name at different levels. Add the level column to say which one.',
            'Row 6: No teacher with this name or email at your school.',
        ], collect($job->result['error_groups'])->pluck('summary')->all());
    }

    public function test_teacher_course_import_follows_the_assignment_rules(): void
    {
        $this->specialties();
        $ada = $this->teacher('Ada Lovelace', 'ada@example.com');
        $alan = $this->teacher('Alan Turing', 'alan@example.com');
        $this->teacher('Grace Hopper', 'grace@example.com');
        $ada->specialties()->attach('swe-1', ['school_branch_id' => 'branch-1']);
        $alan->specialties()->attach('swe-1', ['school_branch_id' => 'branch-1']);

        foreach (['c-1' => ['CS101', 'Algorithms', 'swe-1'], 'c-2' => ['CS102', 'Databases', 'swe-1'], 'c-3' => ['NT101', 'Routing', 'net-1']] as $id => [$code, $title, $specialty]) {
            DB::table('courses')->insert(['id' => $id, 'course_code' => $code, 'course_title' => $title, 'credit' => 3, 'school_branch_id' => 'branch-1', 'status' => 'active', 'semester_id' => 'sem-1']);
            DB::table('course_specialties')->insert(['id' => "cs-{$id}", 'course_id' => $id, 'specialty_id' => $specialty, 'school_branch_id' => 'branch-1']);
        }
        DB::table('teacher_course_preferences')->insert(['id' => 'tcp-1', 'teacher_id' => $alan->id, 'course_id' => 'c-2', 'school_branch_id' => 'branch-1']);

        $csv = "teacher,course\n"
            . "Ada Lovelace,CS101\n"        // 2: assigned
            . "Ada Lovelace,Databases\n"     // 3: course already has a teacher
            . "Ada Lovelace,Routing\n"       // 4: not in her specialties
            . "Grace Hopper,CS101\n"         // 5: duplicate course in file
            . "Ada Lovelace,Compilers\n";    // 6: unknown course

        $response = $this->upload('/api/v1/teacher-course/import', $csv, ['teacher' => 'teacher', 'course' => 'course'])
            ->assertStatus(202);
        $job = $this->job($response->json('data.id'));

        $this->assertSame('Teacher Course Import', $response->json('data.title'));
        $this->assertTrue(DB::table('teacher_course_preferences')->where(['teacher_id' => $ada->id, 'course_id' => 'c-1'])->exists());
        $this->assertSame(1, $job->successful_items);
        $this->assertSame('Teacher', $job->category->name);
        $this->assertEqualsCanonicalizing([
            'Row 3: The course already has a teacher. A course can have only one teacher; change its teacher on the Teacher Course page.',
            "Row 4: The course is not part of any of the teacher's specialties. Assign the teacher to the course's specialty first.",
            'Row 5: Repeats a row higher up in the file; only the first one was imported.',
            'Row 6: No course with this code or title at your school.',
        ], collect($job->result['error_groups'])->pluck('summary')->all());
    }

    public function test_an_import_where_no_row_succeeds_is_failed(): void
    {
        $this->specialties();
        $response = $this->upload('/api/v1/teacher-specialty/import', "teacher,specialty\nNobody,Networking\nNobody Else,Networking\n", ['teacher' => 'teacher', 'specialty' => 'specialty']);
        $job = $this->job($response->json('data.id'));

        $this->assertSame(SystemJob::STATUS_FAILED, $job->status);
        $this->assertSame('None of the 2 rows could be imported. Review the issues, fix the file and import it again.', $job->error_message);
        $this->assertSame('Rows 2–3: No teacher with this name or email at your school.', $job->result['error_groups'][0]['summary']);
    }

    public function test_the_job_endpoints_return_the_job_and_its_grouped_issues(): void
    {
        $this->specialties();
        $id = $this->upload('/api/v1/teacher-specialty/import', "teacher,specialty\nNobody,Networking\n", ['teacher' => 'teacher', 'specialty' => 'specialty'])
            ->json('data.id');

        $this->getJson("/api/v1/system-jobs/{$id}")
            ->assertOk()
            ->assertJsonPath('data.title', 'Teacher Specialty Import')
            ->assertJsonPath('data.is_finished', true)
            ->assertJsonPath('data.status', 'failed');

        $this->getJson("/api/v1/system-jobs/{$id}/errors")
            ->assertOk()
            ->assertJsonPath('data.group_count', 1)
            ->assertJsonPath('data.groups.0.reason', 'not_found')
            ->assertJsonPath('data.groups.0.row_ranges', '2')
            ->assertJsonPath('data.groups.0.examples.0.value', 'Nobody');
    }

    public function test_an_import_that_stopped_reporting_is_marked_failed(): void
    {
        $base = ['context_type' => 'x', 'context_id' => 'branch-1', 'initiated_by_id' => 'admin-1', 'initiated_by_type' => Schooladmin::class, 'queue' => 'database', 'started_at' => now()];
        $stalled = SystemJob::create($base + ['type' => 'teacher_import', 'status' => 'processing']);
        $running = SystemJob::create($base + ['type' => 'teacher_import', 'status' => 'processing']);
        $timetable = SystemJob::create($base + ['type' => 'Semester Timetable Generation', 'status' => 'processing']);
        SystemJob::whereIn('id', [$stalled->id, $timetable->id])->update(['updated_at' => now()->subHour()]);

        $this->assertSame(1, app(StalledImportJobService::class)->failStalled());
        $this->assertSame('failed', $stalled->fresh()->status);
        $this->assertSame('stalled', $stalled->fresh()->error_code);
        $this->assertSame('processing', $running->fresh()->status);
        $this->assertSame('processing', $timetable->fresh()->status, 'Only imports are checked.');
    }
}
