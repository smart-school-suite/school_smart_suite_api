<?php

namespace Tests\Feature\Exam;

use App\Jobs\Exam\CreateExamCandidateJob;
use App\Models\SchoolBranchSetting;
use App\Models\Schooladmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Creating an exam also creates it for the other specialties whose semester
 * has the same dates (ExamAutoCreationService).
 */
class ExamAutoCreationTest extends TestCase
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

        DB::table('levels')->insert([
            ['id' => 'lvl-1', 'name' => 'Level 100', 'level' => 100, 'program_name' => 'l1', 'level_type_id' => 't'],
            ['id' => 'lvl-2', 'name' => 'Level 200', 'level' => 200, 'program_name' => 'l2', 'level_type_id' => 't'],
        ]);
        DB::table('system_academic_years')->insert(['id' => 'sys', 'name' => '2026/2027', 'year_start' => 2026, 'year_end' => 2027]);
        DB::table('semesters')->insert(['id' => 'first', 'name' => 'First Semester', 'count' => 1, 'program_name' => 's1']);
        DB::table('exam_types')->insert([
            ['id' => 'ca-1', 'exam_name' => 'First CA', 'semester' => 'first', 'type' => 'ca', 'program_name' => 'ca1', 'semester_id' => 'first'],
            ['id' => 'exam-1', 'exam_name' => 'First Exam', 'semester' => 'first', 'type' => 'exam', 'program_name' => 'e1', 'semester_id' => 'first'],
        ]);

        // [id, name, level, semester end date, has students]
        foreach ([
            ['swe', 'Software Engineering', 'lvl-1', '2027-01-31', true],
            ['cs', 'Computer Science', 'lvl-2', '2027-01-31', true],
            ['nurse', 'Nursing', 'lvl-1', '2027-01-31', false],
            ['acc', 'Accounting', 'lvl-1', '2027-02-14', true],
        ] as [$id, $name, $level, $end, $students]) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => $level]);
            DB::table('school_academic_years')->insert(['id' => "sy-$id", 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => $id, 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);
            DB::table('school_semesters')->insert(['id' => "sem-$id", 'semester_id' => 'first', 'school_year_id' => "sy-$id", 'start_date' => '2026-09-01', 'end_date' => $end, 'school_branch_id' => 'branch-1']);
            if ($students) {
                DB::table('students')->insert(['id' => "stu-$id", 'name' => $id, 'first_name' => $id, 'last_name' => 'L', 'email' => "$id@example.com", 'password' => 'x', 'username' => $id, 'school_branch_id' => 'branch-1', 'specialty_id' => $id, 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
            }
        }

        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Exam Settings', 'key' => 'setting.category.exam']);
        foreach (['exam.auto_create' => ['boolean', true], 'exam.auto_create_levels' => ['json', ['lvl-1', 'lvl-2']]] as $key => [$type, $value]) {
            DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat']);
            SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$key", 'value' => $value]);
        }
    }

    private function create(string $typeId, float $maxScore)
    {
        return $this->postJson('/api/v1/exam', [
            'school_semester_id' => 'sem-swe',
            'exam_type_id' => $typeId,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-06',
            'max_score' => $maxScore,
        ]);
    }

    private function set(string $key, mixed $value): void
    {
        SchoolBranchSetting::where('setting_defination_id', "def-$key")->first()->update(['value' => $value]);
    }

    /** @return array<string, float> school year => max score of the given exam type */
    private function exams(string $typeId): array
    {
        return DB::table('exams')->where('exam_type_id', $typeId)->orderBy('school_year_id')->pluck('max_score', 'school_year_id')->map(fn($score) => (float) $score)->all();
    }

    public function test_the_exam_is_created_for_specialties_whose_semester_has_the_same_dates(): void
    {
        $response = $this->create('ca-1', 30)->assertCreated();

        $this->assertSame(['sy-cs' => 30.0, 'sy-swe' => 30.0], $this->exams('ca-1'), 'Accounting\'s semester ends on another date.');
        $copy = DB::table('exams')->where('school_year_id', 'sy-cs')->first();
        $this->assertSame(['2026-11-02', '2026-11-06'], [substr($copy->start_date, 0, 10), substr($copy->end_date, 0, 10)]);
        $this->assertCount(1, $response->json('data.auto_created_exams'));
        $this->assertSame(
            [['specialty' => 'Nursing', 'level' => 'Level 100', 'reason' => 'No students in Nursing yet']],
            collect($response->json('data.auto_creation_skipped'))->map(fn($skip) => collect($skip)->only(['specialty', 'level', 'reason'])->all())->all()
        );
        Bus::assertDispatchedTimes(CreateExamCandidateJob::class, 2);
    }

    public function test_an_exam_copy_adds_its_own_specialtys_ca(): void
    {
        DB::table('exams')->insert([
            ['id' => 'ca-swe', 'school_branch_id' => 'branch-1', 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy-swe', 'max_score' => 30, 'start_date' => '2026-10-05', 'end_date' => '2026-10-09'],
            ['id' => 'ca-cs', 'school_branch_id' => 'branch-1', 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy-cs', 'max_score' => 25, 'start_date' => '2026-10-05', 'end_date' => '2026-10-09'],
        ]);

        $this->create('exam-1', 70)->assertCreated();

        $this->assertSame(['sy-cs' => 95.0, 'sy-swe' => 100.0], $this->exams('exam-1'));
    }

    public function test_a_specialty_without_its_ca_or_that_already_has_the_exam_is_skipped(): void
    {
        DB::table('exams')->insert(['id' => 'ca-swe', 'school_branch_id' => 'branch-1', 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy-swe', 'max_score' => 30, 'start_date' => '2026-10-05', 'end_date' => '2026-10-09']);

        $skipped = $this->create('exam-1', 70)->assertCreated()->json('data.auto_creation_skipped');
        $this->assertSame(['sy-swe' => 100.0], $this->exams('exam-1'));
        $this->assertSame('First CA comes first', collect($skipped)->firstWhere('specialty', 'Computer Science')['reason']);

        // Computer Science already has its CA; Software Engineering does not.
        DB::table('exams')->delete();
        DB::table('exams')->insert(['id' => 'ca-cs', 'school_branch_id' => 'branch-1', 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy-cs', 'max_score' => 25, 'start_date' => '2026-10-05', 'end_date' => '2026-10-09']);

        $skipped = $this->create('ca-1', 30)->assertCreated()->json('data.auto_creation_skipped');
        $this->assertSame('First CA already exists', collect($skipped)->firstWhere('specialty', 'Computer Science')['reason']);
    }

    public function test_nothing_is_copied_when_off_or_outside_the_chosen_levels(): void
    {
        $this->set('exam.auto_create_levels', ['lvl-1']);
        $this->create('ca-1', 30)->assertCreated()->assertJsonPath('data.auto_created_exams', []);
        $this->assertSame(['sy-swe' => 30.0], $this->exams('ca-1'), 'Computer Science is at Level 200.');

        DB::table('exams')->delete();
        $this->set('exam.auto_create_levels', ['lvl-1', 'lvl-2']);
        $this->set('exam.auto_create', false);
        $this->create('ca-1', 30)->assertCreated()->assertJsonPath('data.auto_creation_skipped', []);
        $this->assertSame(['sy-swe' => 30.0], $this->exams('ca-1'));
    }
}
