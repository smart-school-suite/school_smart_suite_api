<?php

namespace Tests\Feature\Fees;

use App\Models\Schooladmin;
use App\Models\Student;
use App\Services\RegistrationFee\RegistrationFeeGenerationService;
use App\Services\TuitionFee\TuitionFeeGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Tuition and registration fees, and their transactions, carry their academic year. */
class FeeAcademicYearTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-01 09:00:00');
        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'A', 'name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'a', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        DB::table('levels')->insert(['id' => 'lvl-1', 'name' => 'Level 100', 'level' => 100, 'program_name' => 'l1', 'level_type_id' => 't']);
        DB::table('specialties')->insert(['id' => 'swe', 'specialty_name' => 'SWE', 'registration_fee' => 5000, 'school_fee' => 100000, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        DB::table('system_academic_years')->insert(['id' => 'sys', 'name' => '2026/2027', 'year_start' => 2026, 'year_end' => 2027]);
        DB::table('students')->insert(['id' => 'stu', 'name' => 'Ada Lovelace', 'first_name' => 'Ada', 'last_name' => 'L', 'email' => 'ada@example.com', 'password' => 'x', 'username' => 'ada', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
    }

    private function academicYear(): void
    {
        DB::table('school_academic_years')->insert(['id' => 'sy', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'swe', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);
    }

    private function admit(): void
    {
        $student = Student::find('stu');
        app(RegistrationFeeGenerationService::class)->generate($student, $student->specialty()->firstOrFail());
        app(TuitionFeeGenerationService::class)->syncStudent($student);
    }

    public function test_admission_bills_both_fees_in_the_specialtys_academic_year(): void
    {
        $this->academicYear();
        $this->admit();

        $this->assertSame('sy', DB::table('registration_fees')->value('school_academic_year_id'));
        $this->assertSame('sy', DB::table('tuition_fees')->value('school_academic_year_id'));
    }

    public function test_a_student_admitted_before_the_academic_year_gets_it_when_tuition_is_billed(): void
    {
        $this->admit();
        $this->assertNull(DB::table('registration_fees')->value('school_academic_year_id'), 'No academic year yet.');

        $this->academicYear();
        app(TuitionFeeGenerationService::class)->syncStudent(Student::find('stu'));

        $this->assertSame('sy', DB::table('registration_fees')->value('school_academic_year_id'));
    }

    public function test_every_fee_list_shows_the_academic_year(): void
    {
        $this->academicYear();
        $this->admit();
        $tuitionId = DB::table('tuition_fees')->value('id');
        $registrationId = DB::table('registration_fees')->value('id');
        DB::table('tuition_fee_transactions')->insert(['id' => 'tt', 'transaction_id' => 'TX1', 'amount' => 500, 'payment_method' => 'cash', 'school_branch_id' => 'branch-1', 'tuition_id' => $tuitionId]);
        DB::table('registration_fee_transactions')->insert(['id' => 'rt', 'transaction_id' => 'TX2', 'amount' => 500, 'payment_method' => 'cash', 'school_branch_id' => 'branch-1', 'registrationfee_id' => $registrationId]);

        $expected = ['id' => 'sy', 'name' => '2026/2027', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31'];
        foreach (['/api/v1/tuition-fee', '/api/v1/registration-fees', '/api/v1/tuition-fee/transaction', '/api/v1/registration-fees/transactions'] as $url) {
            $this->getJson($url)->assertOk()
                ->assertJsonPath('data.0.academic_year', $expected)
                ->assertJsonPath('data.0.academic_year_name', '2026/2027');
        }
    }

    public function test_the_migration_gives_existing_registration_fees_their_academic_year(): void
    {
        $this->academicYear();
        DB::table('students')->insert(['id' => 'stu-2', 'name' => 'Bob', 'first_name' => 'Bob', 'last_name' => 'B', 'email' => 'bob@example.com', 'password' => 'x', 'username' => 'bob', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'school_academic_year_id' => 'sy', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        DB::table('registration_fees')->insert([
            ['id' => 'from-tuition', 'amount' => 5000, 'student_id' => 'stu', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe'],
            ['id' => 'from-student', 'amount' => 5000, 'student_id' => 'stu-2', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe'],
            ['id' => 'old-specialty', 'amount' => 5000, 'student_id' => 'stu-2', 'school_branch_id' => 'branch-1', 'specialty_id' => 'old'],
        ]);
        DB::table('tuition_fees')->insert(['id' => 'tf', 'tution_fee_total' => 1, 'student_id' => 'stu', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'school_academic_year_id' => 'sy']);

        $migration = require database_path('migrations/04_finance/2026_11_21_150000_add_school_academic_year_to_registration_fees.php');
        $migration->up();
        $migration->up();

        $this->assertSame(
            ['from-student' => 'sy', 'from-tuition' => 'sy', 'old-specialty' => null],
            DB::table('registration_fees')->orderBy('id')->pluck('school_academic_year_id', 'id')->all(),
        );
    }
}
