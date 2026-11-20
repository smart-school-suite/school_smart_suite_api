<?php

namespace Tests\Feature\Exam;

use App\Models\Schooladmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Exams are created for a school semester (ExamCreationPolicy). */
class ExamCreationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-01 09:00:00');
        Bus::fake();

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'A', 'name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'a', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        DB::table('levels')->insert(['id' => 'lvl-1', 'name' => 'Level 100', 'level' => 100, 'program_name' => 'l1', 'level_type_id' => 't']);
        foreach (['swe' => 'Software Engineering', 'nurse' => 'Nursing'] as $id => $name) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        }
        DB::table('system_academic_years')->insert(['id' => 'sys', 'name' => '2026/2027', 'year_start' => 2026, 'year_end' => 2027]);
        DB::table('school_academic_years')->insert([
            ['id' => 'sy', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'swe', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1'],
            ['id' => 'sy-nurse', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'nurse', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1'],
        ]);
        DB::table('students')->insert(['id' => 'stu', 'name' => 'Ada', 'first_name' => 'Ada', 'last_name' => 'L', 'email' => 'ada@example.com', 'password' => 'x', 'username' => 'ada', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);

        DB::table('semesters')->insert([
            ['id' => 'first', 'name' => 'First Semester', 'count' => 1, 'program_name' => 's1'],
            ['id' => 'second', 'name' => 'Second Semester', 'count' => 2, 'program_name' => 's2'],
        ]);
        DB::table('exam_types')->insert([
            ['id' => 'ca-1', 'exam_name' => 'First CA', 'semester' => 'first', 'type' => 'ca', 'program_name' => 'ca1', 'semester_id' => 'first'],
            ['id' => 'exam-1', 'exam_name' => 'First Exam', 'semester' => 'first', 'type' => 'exam', 'program_name' => 'e1', 'semester_id' => 'first'],
            ['id' => 'resit-1', 'exam_name' => 'First Resit', 'semester' => 'first', 'type' => 'resit', 'program_name' => 'r1', 'semester_id' => 'first'],
            ['id' => 'ca-2', 'exam_name' => 'Second CA', 'semester' => 'second', 'type' => 'ca', 'program_name' => 'ca2', 'semester_id' => 'second'],
            ['id' => 'exam-2', 'exam_name' => 'Second Exam', 'semester' => 'second', 'type' => 'exam', 'program_name' => 'e2', 'semester_id' => 'second'],
        ]);
        DB::table('school_semesters')->insert([
            ['id' => 'sem-1', 'semester_id' => 'first', 'school_year_id' => 'sy', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31', 'school_branch_id' => 'branch-1'],
            ['id' => 'sem-2', 'semester_id' => 'second', 'school_year_id' => 'sy', 'start_date' => '2027-02-01', 'end_date' => '2027-06-30', 'school_branch_id' => 'branch-1'],
            ['id' => 'nurse-1', 'semester_id' => 'first', 'school_year_id' => 'sy-nurse', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31', 'school_branch_id' => 'branch-1'],
        ]);
    }

    private function creationOptions(): array
    {
        return $this->getJson('/api/v1/exam/creation-options')->assertOk()->json('data');
    }

    private function semester(array $options, string $id): array
    {
        return collect($options['semesters'])->firstWhere('id', $id);
    }

    private function create(array $data)
    {
        return $this->postJson('/api/v1/exam', $data + ['start_date' => '2026-11-02', 'end_date' => '2026-11-06', 'max_score' => 30]);
    }

    public function test_the_first_unused_semester_is_recommended_with_its_ca(): void
    {
        $options = $this->creationOptions();
        $first = $this->semester($options, 'sem-1');

        $this->assertSame('sem-1', $options['semesters'][0]['id'], 'Recommended semesters come first.');
        $this->assertSame([true, 'unused', 'ongoing', 'ca-1'], [$first['recommended'], $first['usage'], $first['status'], $first['next_exam_type_id']]);
        $this->assertSame(['First CA', 'First Exam'], array_column($first['exam_types'], 'name'), 'Resit types are not offered.');
        $this->assertSame([true, false], array_column($first['exam_types'], 'available'));
        $this->assertSame(['ca_missing', 'First CA comes first', 'create_ca'], [$first['exam_types'][1]['issue']['code'], $first['exam_types'][1]['issue']['title'], $first['exam_types'][1]['issue']['action']]);
        $this->assertSame(['2026-10-01', '2027-01-31'], [$first['fields']['start_date']['min'], $first['fields']['start_date']['max']], 'Today to the end of the semester.');

        $this->assertFalse($this->semester($options, 'sem-2')['recommended'], 'One recommendation per academic year.');
        $nurse = $this->semester($options, 'nurse-1');
        $this->assertFalse($nurse['available']);
        $this->assertSame(['no_students', 'No students in Nursing yet', 'add_students', 'Add or import students into Nursing, then come back to create its exams.'], [$nurse['issue']['code'], $nurse['issue']['title'], $nurse['issue']['action'], $nurse['issue']['next_step']]);
        $this->assertStringContainsString('We found the First Semester of Nursing · Level 100 (2026/2027), but no students are enrolled in Nursing', $nurse['issue']['message']);
    }

    public function test_a_used_semester_recommends_its_next_exam_then_the_next_semester(): void
    {
        $this->create(['school_semester_id' => 'sem-1', 'exam_type_id' => 'ca-1'])->assertCreated();

        $first = $this->semester($this->creationOptions(), 'sem-1');
        $this->assertSame([true, 'partial', 'exam-1'], [$first['recommended'], $first['usage'], $first['next_exam_type_id']]);
        $this->assertSame('First CA already exists; First Exam is next.', $first['recommendation']);
        $this->assertSame([true, 30.0], [$first['exam_types'][0]['created'], (float) $first['exam_types'][1]['ca_max_score']]);

        $this->create(['school_semester_id' => 'sem-1', 'exam_type_id' => 'exam-1', 'max_score' => 70])->assertCreated();

        $options = $this->creationOptions();
        $this->assertSame([false, 'complete', false], [$this->semester($options, 'sem-1')['available'], $this->semester($options, 'sem-1')['usage'], $this->semester($options, 'sem-1')['recommended']]);
        $second = $this->semester($options, 'sem-2');
        $this->assertSame([true, 'ca-2'], [$second['recommended'], $second['next_exam_type_id']]);
        $this->assertStringContainsString('No exam has been created for the Second Semester yet', $second['recommendation']);
    }

    public function test_creating_takes_the_academic_year_from_the_semester_and_adds_the_ca_to_the_exam(): void
    {
        $this->create(['school_semester_id' => 'sem-1', 'exam_type_id' => 'ca-1'])->assertCreated();
        $this->create(['school_semester_id' => 'sem-1', 'exam_type_id' => 'exam-1', 'max_score' => 70])->assertCreated();

        $exams = DB::table('exams')->orderBy('max_score')->get(['exam_type_id', 'school_year_id', 'max_score']);
        $this->assertSame([['ca-1', 'sy', 30.0], ['exam-1', 'sy', 100.0]], $exams->map(fn($e) => [$e->exam_type_id, $e->school_year_id, (float) $e->max_score])->all());
    }

    public function test_creating_refuses_what_the_options_do_not_offer(): void
    {
        $this->postJson('/api/v1/exam', ['school_year_id' => 'sy', 'exam_type_id' => 'ca-1', 'start_date' => '2026-11-02', 'end_date' => '2026-11-06', 'max_score' => 30])
            ->assertStatus(422)->assertJsonValidationErrors('school_semester_id');
        $this->create(['school_semester_id' => 'sem-1', 'exam_type_id' => 'ca-2'])->assertStatus(422)->assertJsonPath('errors.title', 'Exam Type Not In This Semester');
        $this->create(['school_semester_id' => 'sem-1', 'exam_type_id' => 'exam-1'])->assertStatus(422)->assertJsonPath('errors.title', 'First CA comes first');
        $this->create(['school_semester_id' => 'nurse-1', 'exam_type_id' => 'ca-1'])->assertStatus(422)->assertJsonPath('errors.title', 'No students in Nursing yet');
        $this->create(['school_semester_id' => 'missing', 'exam_type_id' => 'ca-1'])->assertStatus(404);
        $this->create(['school_semester_id' => 'sem-1', 'exam_type_id' => 'ca-1', 'end_date' => '2027-02-10'])
            ->assertStatus(422)->assertJsonPath('errors.title', 'End date Not Allowed');
        $this->create(['school_semester_id' => 'sem-1', 'exam_type_id' => 'ca-1', 'start_date' => '2026-09-15'])
            ->assertStatus(422)->assertJsonPath('errors.description', 'It must be on or after 1 Oct 2026: it cannot be in the past.');

        $this->create(['school_semester_id' => 'sem-1', 'exam_type_id' => 'ca-1'])->assertCreated();
        $this->create(['school_semester_id' => 'sem-1', 'exam_type_id' => 'ca-1'])->assertStatus(409)->assertJsonPath('errors.title', 'First CA already exists');
        $this->assertSame(1, DB::table('exams')->count());
    }

    public function test_an_ended_semester_takes_no_new_exams_and_years_without_semesters_are_listed(): void
    {
        Carbon::setTestNow('2027-02-15 09:00:00');
        DB::table('school_academic_years')->insert(['id' => 'sy-bare', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'nurse', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);

        $options = $this->creationOptions();
        $first = $this->semester($options, 'sem-1');

        $this->assertSame(['ended', false], [$first['status'], $first['available']]);
        $this->assertSame(['semester_ended', 'The First Semester has ended'], [$first['issue']['code'], $first['issue']['title']]);
        $this->assertStringContainsString('ended on 31 Jan 2027', $first['issue']['message']);
        $this->assertTrue($this->semester($options, 'sem-2')['recommended'], 'The next semester takes over.');
        $this->assertSame(['sy-bare'], array_column($options['academic_years_without_semesters'], 'id'));
    }

    public function test_the_summary_says_what_was_found_and_what_to_do_next(): void
    {
        DB::table('school_academic_years')->insert(['id' => 'sy-bare', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'nurse', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);

        $summary = $this->creationOptions()['summary'];

        $this->assertSame('We found 3 semesters: 2 can take a new exam.', $summary['headline']);
        $this->assertSame(['no_students', 'no_semesters'], array_column($summary['findings'], 'code'));
        $this->assertSame('1 semester is waiting for students: Nursing has no students enrolled yet.', $summary['findings'][0]['text']);
        $this->assertSame(['add_students', 'Add or import students into Nursing to create its exams.'], [$summary['findings'][0]['action'], $summary['findings'][0]['next_step']]);
        $this->assertSame('1 academic year has no semesters yet: Nursing · Level 100 (2026/2027).', $summary['findings'][1]['text']);
    }

    public function test_a_school_without_semesters_is_told_to_create_them(): void
    {
        DB::table('school_semesters')->delete();

        $summary = $this->creationOptions()['summary'];

        $this->assertSame("We didn't find any semester to create an exam for.", $summary['headline']);
        $this->assertSame(['no_semesters', 'manage_semesters'], [$summary['findings'][0]['code'], $summary['findings'][0]['action']]);
    }

    public function test_an_unknown_exam_path_is_not_read_as_an_exam_id(): void
    {
        $this->getJson('/api/v1/exam/creation-optionz')->assertNotFound()->assertJsonMissingPath('errors.title');
    }
}
