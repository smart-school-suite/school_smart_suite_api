<?php

namespace Tests\Feature\Fees;

use App\Events\AcademicYear\SchoolAcademicYearCreated;
use App\Events\Student\StudentRegistered;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\RegistrationFee;
use App\Models\Role;
use App\Models\SchoolBranchSetting;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Services\AcademicYear\SchoolAcademicYearService;
use App\Services\Auth\Student\CreateStudentService;
use App\Services\TuitionFee\TuitionFeeGenerationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentFeeGenerationTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->school = (object) ['id' => 'branch-1'];

        foreach ([['sys-2025', 2025], ['sys-2026', 2026], ['sys-2027', 2027]] as [$id, $start]) {
            DB::table('system_academic_years')->insert(['id' => $id, 'name' => "$start/" . ($start + 1), 'year_start' => $start, 'year_end' => $start + 1]);
        }
        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        foreach ([['swe-1', 'lvl-1', 50000, 250000], ['swe-2', 'lvl-2', 60000, 400000]] as [$id, $level, $registration, $tuition]) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => 'Software Engineering', 'registration_fee' => $registration, 'school_fee' => $tuition, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'computing', 'level_id' => $level]);
        }
    }

    private function student(string $id, string $specialtyId = 'swe-1', bool $droppedOut = false, string $branch = 'branch-1'): Student
    {
        DB::table('students')->insert(['id' => $id, 'name' => $id, 'first_name' => $id, 'last_name' => $id, 'email' => "$id@example.com", 'password' => 'x', 'username' => $id, 'school_branch_id' => $branch, 'specialty_id' => $specialtyId, 'dropout_status' => $droppedOut, 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);

        return Student::find($id);
    }

    /** An academic year inserted directly, so no event fires. */
    private function existingYear(string $id, string $specialtyId, string $start, string $end, string $system = 'sys-2026'): SchoolAcademicYear
    {
        DB::table('school_academic_years')->insert(['id' => $id, 'specialty_id' => $specialtyId, 'school_branch_id' => 'branch-1', 'system_academic_year_id' => $system, 'start_date' => $start, 'end_date' => $end]);

        return SchoolAcademicYear::find($id);
    }

    private function createYear(string $specialtyId, string $system, string $start, string $end): SchoolAcademicYear
    {
        return app(SchoolAcademicYearService::class)->createSchoolAcademicYear([
            'specialty_id' => $specialtyId,
            'system_academic_year_id' => $system,
            'start_date' => $start,
            'end_date' => $end,
        ], $this->school);
    }

    private function future(int $months): string
    {
        return now()->addMonths($months)->toDateString();
    }

    // ── Student registration ────────────────────────────────────────────

    public function test_registration_without_an_academic_year_bills_the_registration_fee_and_defers_tuition(): void
    {
        $student = $this->student('stu-1');

        StudentRegistered::dispatch($student);

        $this->assertSame(50000.0, RegistrationFee::where('student_id', 'stu-1')->where('specialty_id', 'swe-1')->value('amount'));
        $this->assertSame('not paid', RegistrationFee::where('student_id', 'stu-1')->value('status'));
        $this->assertSame(0, TuitionFees::count());
        $this->assertNull($student->fresh()->school_academic_year_id);
    }

    public function test_registration_assigns_the_latest_applicable_academic_year_and_bills_its_tuition(): void
    {
        $this->existingYear('ended', 'swe-1', now()->subMonths(14)->toDateString(), now()->subMonths(2)->toDateString(), 'sys-2025');
        $this->existingYear('current', 'swe-1', now()->subMonths(2)->toDateString(), $this->future(8), 'sys-2026');
        $this->existingYear('next', 'swe-1', $this->future(10), $this->future(20), 'sys-2027');
        $this->existingYear('other-level', 'swe-2', $this->future(10), $this->future(30), 'sys-2027');
        $student = $this->student('stu-1');

        StudentRegistered::dispatch($student);

        $this->assertSame('next', $student->fresh()->school_academic_year_id);
        $tuition = TuitionFees::where('student_id', 'stu-1')->sole();
        $this->assertSame('next', $tuition->school_academic_year_id);
        $this->assertSame('swe-1', $tuition->specialty_id);
        $this->assertEquals(250000, $tuition->tution_fee_total);
        $this->assertEquals(0, $tuition->amount_paid);
    }

    public function test_an_ended_academic_year_is_not_applicable_to_a_new_student(): void
    {
        $this->existingYear('ended', 'swe-1', now()->subMonths(14)->toDateString(), now()->subDay()->toDateString());
        $student = $this->student('stu-1');

        StudentRegistered::dispatch($student);

        $this->assertSame(0, TuitionFees::count());
        $this->assertNull($student->fresh()->school_academic_year_id);
    }

    public function test_repeated_registration_events_do_not_duplicate_fee_records(): void
    {
        $this->existingYear('current', 'swe-1', now()->subMonth()->toDateString(), $this->future(10));
        $student = $this->student('stu-1');

        StudentRegistered::dispatch($student);
        StudentRegistered::dispatch($student->fresh());

        $this->assertSame(1, RegistrationFee::count());
        $this->assertSame(1, TuitionFees::count());
    }

    public function test_the_create_student_service_bills_the_new_student_in_its_transaction(): void
    {
        Role::create(['name' => 'student', 'guard_name' => 'student']);
        $this->existingYear('current', 'swe-2', now()->subMonth()->toDateString(), $this->future(10));

        $student = app(CreateStudentService::class)->createStudent([
            'name' => 'Jane Doe', 'first_name' => 'Jane', 'last_name' => 'Doe', 'email' => 'jane@example.com',
            'gender_id' => 'g', 'student_source_id' => 's', 'guardian_id' => 'g', 'relationship_id' => 'r',
            'specialty_id' => 'swe-2', 'student_batch_id' => 'b',
        ], $this->school, null);

        $this->assertSame('current', $student->fresh()->school_academic_year_id);
        $this->assertSame(60000.0, RegistrationFee::where('student_id', $student->id)->value('amount'));
        $this->assertEquals(400000, TuitionFees::where('student_id', $student->id)->where('school_academic_year_id', 'current')->value('tution_fee_total'));
    }

    // ── Academic year creation ──────────────────────────────────────────

    public function test_creating_an_academic_year_bills_and_assigns_its_specialtys_eligible_students(): void
    {
        $this->student('stu-1');
        $this->student('stu-2');
        $this->student('dropped', droppedOut: true);
        $this->student('other-level', 'swe-2');
        $this->student('other-branch', branch: 'branch-2');

        $year = $this->createYear('swe-1', 'sys-2026', now()->subWeek()->toDateString(), $this->future(11));

        $this->assertEqualsCanonicalizing(['stu-1', 'stu-2'], TuitionFees::where('school_academic_year_id', $year->id)->pluck('student_id')->all());
        $this->assertSame(2, TuitionFees::count());
        $this->assertEquals([250000, 250000], TuitionFees::pluck('tution_fee_total')->map(fn($v) => (float) $v)->all());
        $this->assertEqualsCanonicalizing(['stu-1', 'stu-2'], Student::where('school_academic_year_id', $year->id)->pluck('id')->all());
        $this->assertSame(0, RegistrationFee::count(), 'Academic year creation bills tuition only.');
    }

    public function test_repeating_academic_year_generation_does_not_duplicate_tuition(): void
    {
        $this->student('stu-1');
        $year = $this->createYear('swe-1', 'sys-2026', now()->subWeek()->toDateString(), $this->future(11));

        SchoolAcademicYearCreated::dispatch($year);
        app(TuitionFeeGenerationService::class)->generateForAcademicYear(Student::find('stu-1'), $year);

        $this->assertSame(1, TuitionFees::count());
    }

    public function test_each_new_academic_year_is_a_new_tuition_obligation(): void
    {
        $this->student('stu-1');

        $first = $this->createYear('swe-1', 'sys-2026', now()->subWeek()->toDateString(), $this->future(11));
        $second = $this->createYear('swe-1', 'sys-2027', $this->future(12), $this->future(24));

        $this->assertSame(1, TuitionFees::where('school_academic_year_id', $first->id)->count());
        $this->assertSame(1, TuitionFees::where('school_academic_year_id', $second->id)->count());
        $this->assertSame($second->id, Student::find('stu-1')->school_academic_year_id);
    }

    public function test_an_older_academic_year_bills_its_students_without_replacing_their_later_academic_year(): void
    {
        $this->student('stu-1');
        $later = $this->createYear('swe-1', 'sys-2027', $this->future(12), $this->future(24));

        $earlier = $this->createYear('swe-1', 'sys-2026', now()->subWeek()->toDateString(), $this->future(11));

        $this->assertSame(1, TuitionFees::where('school_academic_year_id', $earlier->id)->count());
        $this->assertSame($later->id, Student::find('stu-1')->school_academic_year_id);
    }

    public function test_an_academic_year_that_has_already_ended_bills_no_one(): void
    {
        $this->student('stu-1');

        $this->createYear('swe-1', 'sys-2025', now()->subMonths(14)->toDateString(), now()->subMonths(2)->toDateString());

        $this->assertSame(0, TuitionFees::count());
        $this->assertNull(Student::find('stu-1')->school_academic_year_id);
    }

    public function test_a_deferred_student_is_billed_once_their_academic_year_is_created(): void
    {
        $student = $this->student('stu-1');
        StudentRegistered::dispatch($student);
        $this->assertSame(0, TuitionFees::count());

        $year = $this->createYear('swe-1', 'sys-2026', now()->subWeek()->toDateString(), $this->future(11));

        $this->assertSame(1, TuitionFees::where('student_id', 'stu-1')->where('school_academic_year_id', $year->id)->count());
        $this->assertSame($year->id, $student->fresh()->school_academic_year_id);
    }

    public function test_a_tuition_record_created_before_academic_years_were_tracked_is_linked_not_duplicated(): void
    {
        $this->student('stu-1');
        $legacy = TuitionFees::create(['student_id' => 'stu-1', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'tution_fee_total' => 250000]);
        DB::table('tuition_fee_transactions')->insert(['id' => 'txn-1', 'transaction_id' => 'TXN-1', 'amount' => 1000, 'payment_method' => 'cash', 'tuition_id' => $legacy->id, 'school_branch_id' => 'branch-1']);

        $year = $this->createYear('swe-1', 'sys-2026', now()->subWeek()->toDateString(), $this->future(11));

        $this->assertSame(1, TuitionFees::count());
        $this->assertSame($year->id, $legacy->fresh()->school_academic_year_id);
        $this->assertEquals(1000, $legacy->fresh()->amount_paid);
    }

    public function test_automatically_created_academic_years_bill_their_own_students(): void
    {
        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Academic Year Settings', 'key' => 'setting.category.academic_year']);
        DB::table('setting_definations')->insert(['id' => 'def-enabled', 'name' => 'enabled', 'key' => 'academic_year.auto_create_enabled', 'data_type' => 'boolean', 'setting_category_id' => 'cat']);
        DB::table('setting_definations')->insert(['id' => 'def-scope', 'name' => 'scope', 'key' => 'academic_year.auto_create_across_department_specialty', 'data_type' => 'boolean', 'setting_category_id' => 'cat']);
        DB::table('setting_definations')->insert(['id' => 'def-levels', 'name' => 'levels', 'key' => 'academic_year.auto_create_levels', 'data_type' => 'json', 'setting_category_id' => 'cat']);
        SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => 'def-enabled', 'value' => true]);
        SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => 'def-scope', 'value' => true]);
        SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => 'def-levels', 'value' => ['lvl-1', 'lvl-2']]);
        $this->student('stu-1');
        $this->student('stu-2', 'swe-2');

        $this->createYear('swe-1', 'sys-2026', now()->subWeek()->toDateString(), $this->future(11));

        $copy = SchoolAcademicYear::where('specialty_id', 'swe-2')->sole();
        $this->assertEquals(400000, TuitionFees::where('student_id', 'stu-2')->where('school_academic_year_id', $copy->id)->value('tution_fee_total'));
        $this->assertSame($copy->id, Student::find('stu-2')->school_academic_year_id);
    }

    public function test_the_database_rejects_a_second_tuition_record_for_the_same_student_and_academic_year(): void
    {
        $this->student('stu-1');
        $this->existingYear('current', 'swe-1', now()->subMonth()->toDateString(), $this->future(10));
        $row = ['student_id' => 'stu-1', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'school_academic_year_id' => 'current', 'tution_fee_total' => 1];
        TuitionFees::create($row);

        $this->expectException(UniqueConstraintViolationException::class);
        TuitionFees::create($row);
    }
}
