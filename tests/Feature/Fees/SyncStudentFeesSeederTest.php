<?php

namespace Tests\Feature\Fees;

use App\Models\RegistrationFee;
use App\Models\Student;
use App\Models\TuitionFees;
use Database\Seeders\SyncStudentFeesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SyncStudentFeesSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('system_academic_years')->insert(['id' => 'sys', 'name' => 'Y', 'year_start' => 2025, 'year_end' => 2026]);
        foreach ([['swe-1', 'lvl-1', 250000], ['swe-2', 'lvl-2', 400000]] as [$id, $level, $tuition]) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => 'Software Engineering', 'registration_fee' => 50000, 'school_fee' => $tuition, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'computing', 'level_id' => $level]);
        }
        $year = fn($id, $specialty, $start, $end) => DB::table('school_academic_years')->insert(['id' => $id, 'specialty_id' => $specialty, 'school_branch_id' => 'branch-1', 'system_academic_year_id' => 'sys', 'start_date' => $start, 'end_date' => $end]);
        $year('past-swe-1', 'swe-1', now()->subMonths(20)->toDateString(), now()->subMonths(9)->toDateString());
        $year('current-swe-1', 'swe-1', now()->subMonths(2)->toDateString(), now()->addMonths(9)->toDateString());
        $year('current-swe-2', 'swe-2', now()->subMonths(2)->toDateString(), now()->addMonths(9)->toDateString());

        foreach ([['new', 'swe-1', false], ['old', 'swe-1', false], ['promoted', 'swe-2', false], ['dropped', 'swe-1', true]] as [$id, $specialty, $dropped]) {
            DB::table('students')->insert(['id' => $id, 'name' => $id, 'first_name' => $id, 'last_name' => $id, 'email' => "$id@example.com", 'password' => 'x', 'username' => $id, 'school_branch_id' => 'branch-1', 'specialty_id' => $specialty, 'dropout_status' => $dropped, 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        }

        // Pre-existing data: registration fees for all but "new"; an unlinked tuition
        // record from last year for "old", and one for "promoted" at its current level.
        foreach (['old', 'promoted', 'dropped'] as $id) {
            DB::table('registration_fees')->insert(['id' => "reg-$id", 'amount' => 1, 'status' => 'paid', 'student_id' => $id, 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1']);
        }
        DB::table('tuition_fees')->insert(['id' => 'legacy-old', 'student_id' => 'old', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'tution_fee_total' => 250000, 'created_at' => now()->subMonths(15), 'updated_at' => now()->subMonths(15)]);
        DB::table('tuition_fees')->insert(['id' => 'legacy-promoted', 'student_id' => 'promoted', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-2', 'tution_fee_total' => 400000, 'created_at' => now()->subMonths(1), 'updated_at' => now()->subMonths(1)]);
    }

    public function test_it_links_legacy_records_bills_open_years_and_assigns_academic_years(): void
    {
        $this->seed(SyncStudentFeesSeeder::class);

        $this->assertSame('past-swe-1', TuitionFees::find('legacy-old')->school_academic_year_id, "Last year's record is linked to the year it was billed in.");
        $this->assertSame('current-swe-2', TuitionFees::find('legacy-promoted')->school_academic_year_id, 'A current record is linked, not duplicated.');
        $this->assertSame(1, TuitionFees::where('student_id', 'old')->where('school_academic_year_id', 'current-swe-1')->count(), 'This year is a new obligation.');
        $this->assertSame(1, TuitionFees::where('student_id', 'new')->where('school_academic_year_id', 'current-swe-1')->count());
        $this->assertSame(1, TuitionFees::where('student_id', 'promoted')->count());
        $this->assertSame(0, TuitionFees::where('student_id', 'dropped')->count());

        $this->assertSame(['current-swe-1', 'current-swe-1', 'current-swe-2'], Student::whereIn('id', ['new', 'old', 'promoted'])->orderBy('id')->pluck('school_academic_year_id')->all());
        $this->assertNull(Student::find('dropped')->school_academic_year_id);

        $this->assertSame(50000.0, RegistrationFee::where('student_id', 'new')->sole()->amount);
        $this->assertSame(4, RegistrationFee::count());
    }

    public function test_running_it_again_changes_nothing(): void
    {
        $this->seed(SyncStudentFeesSeeder::class);
        $tuition = TuitionFees::orderBy('id')->get(['id', 'school_academic_year_id'])->toArray();
        $registration = RegistrationFee::count();

        $this->seed(SyncStudentFeesSeeder::class);

        $this->assertSame($tuition, TuitionFees::orderBy('id')->get(['id', 'school_academic_year_id'])->toArray());
        $this->assertSame($registration, RegistrationFee::count());
    }
}
