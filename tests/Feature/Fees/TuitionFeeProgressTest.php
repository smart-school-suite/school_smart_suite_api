<?php

namespace Tests\Feature\Fees;

use App\Models\FeeScheduleSlot;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Services\TuitionFee\TuitionFeeProgressService;
use App\Services\TuitionFee\TuitionFeeScheduleService;
use App\Services\TuitionFee\TuitionFeeScheduleSlotService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TuitionFeeProgressTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-16 10:00:00');
        $this->school = (object) ['id' => 'branch-1'];

        DB::table('system_academic_years')->insert(['id' => 'sys-2026', 'name' => '2026/2027', 'year_start' => 2026, 'year_end' => 2027]);
        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        foreach ([['swe-1', 'lvl-1'], ['swe-2', 'lvl-2']] as [$id, $level]) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 100000, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'computing', 'level_id' => $level]);
            DB::table('school_academic_years')->insert(['id' => "year-$id", 'specialty_id' => $id, 'school_branch_id' => 'branch-1', 'system_academic_year_id' => 'sys-2026', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31']);
            DB::table('fee_schedules')->insert(['id' => "sched-$id", 'school_year_id' => "year-$id", 'school_branch_id' => 'branch-1']);
        }
        foreach ([1 => 'First Installment', 2 => 'Second Installment', 3 => 'Third Installment'] as $count => $name) {
            DB::table('installments')->insert(['id' => "inst-$count", 'name' => $name, 'program_name' => "i$count", 'code' => "I$count", 'count' => $count]);
        }
        // swe-1: 50% by Oct 1, 30% by Dec 1, 20% by Mar 1.
        foreach ([['s1', 1, '2026-10-01', 50], ['s2', 2, '2026-12-01', 30], ['s3', 3, '2027-03-01', 20]] as [$id, $count, $due, $pct]) {
            DB::table('fee_sched_installments')->insert(['id' => $id, 'due_date' => $due, 'percentage' => $pct, 'installment_id' => "inst-$count", 'fee_schedule_id' => 'sched-swe-1', 'school_branch_id' => 'branch-1']);
        }

        DB::table('students')->insert(['id' => 'stu-1', 'name' => 'S', 'first_name' => 'S', 'last_name' => 'S', 'email' => 's@example.com', 'password' => 'x', 'username' => 's', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'school_academic_year_id' => 'year-swe-1', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        DB::table('tuition_fees')->insert(['id' => 'fee-1', 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'school_academic_year_id' => 'year-swe-1', 'amount_paid' => 0, 'tution_fee_total' => 80000]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function pay(float $amount, string $at, string $feeId = 'fee-1'): void
    {
        DB::table('tuition_fee_transactions')->insert(['id' => uniqid('t', true), 'transaction_id' => uniqid(), 'amount' => $amount, 'payment_method' => 'cash', 'tuition_id' => $feeId, 'school_branch_id' => 'branch-1', 'created_at' => $at, 'updated_at' => $at]);
    }

    private function progress(): array
    {
        return app(TuitionFeeProgressService::class)->forTuitionFee(TuitionFees::find('fee-1'));
    }

    public function test_installment_amounts_follow_the_students_own_tuition_total(): void
    {
        $progress = $this->progress();

        $this->assertSame([40000.0, 24000.0, 16000.0], array_column($progress['installments'], 'expected_amount'));
        $this->assertSame(['unpaid', 'unpaid', 'unpaid'], array_column($progress['installments'], 'status'));
        $this->assertSame([true, false, false], array_column($progress['installments'], 'is_overdue'));
        $this->assertSame(80000.0, $progress['amount_left']);
    }

    public function test_payments_fill_installments_in_due_date_order_and_rate_punctuality(): void
    {
        $this->pay(30000, '2026-09-15 09:00:00');
        $this->pay(20000, '2026-10-05 09:00:00'); // completes the first installment late
        $this->pay(24000, '2026-11-20 09:00:00'); // completes the second on time

        $slots = $this->progress()['installments'];

        $this->assertSame(['completed', 'completed', 'inprogress'], array_column($slots, 'status'));
        $this->assertSame(['late', 'paypunctual', 'pending'], array_column($slots, 'gramification'));
        $this->assertSame([40000.0, 24000.0, 10000.0], array_column($slots, 'amount_paid'));
        $this->assertSame(6000.0, $slots[2]['amount_left']);
        $this->assertSame(62.5, $slots[2]['percentage_paid']);
        $this->assertSame(92.5, $this->progress()['percentage_paid']);
    }

    public function test_changing_the_schedule_changes_progress_without_any_resync(): void
    {
        $this->pay(40000, '2026-09-15 09:00:00');
        FeeScheduleSlot::find('s1')->update(['percentage' => 60]);

        $first = $this->progress()['installments'][0];

        $this->assertSame(48000.0, $first['expected_amount']);
        $this->assertSame('inprogress', $first['status']);
    }

    public function test_the_student_schedule_endpoints_group_derived_progress_by_level(): void
    {
        DB::table('tuition_fees')->insert(['id' => 'fee-2', 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-2', 'school_academic_year_id' => 'year-swe-2', 'amount_paid' => 0, 'tution_fee_total' => 100000]);
        DB::table('fee_sched_installments')->insert(['id' => 'l2', 'due_date' => '2027-01-01', 'percentage' => 100, 'installment_id' => 'inst-1', 'fee_schedule_id' => 'sched-swe-2', 'school_branch_id' => 'branch-1']);
        $this->pay(40000, '2026-09-15 09:00:00');
        $service = app(TuitionFeeScheduleService::class);
        $student = Student::find('stu-1');

        $all = $service->getStudentFeeSchedule($this->school, $student);
        $this->assertSame(['lvl-1', 'lvl-2'], array_column($all, 'level_id'));
        $this->assertCount(3, $all[0]['installment_slots']);
        $this->assertSame('paypunctual', $all[0]['installment_slots'][0]['gramification']);
        $this->assertSame(100000.0, $all[1]['installment_slots'][0]['expected_amount']);
        $this->assertSame('2026/2027', $all[0]['tuition_fees'][0]['academic_year']);

        $this->assertSame(['lvl-2'], array_column($service->getStudentFeeScheduleLevelId($this->school, $student, 'lvl-2'), 'level_id'));

        $current = $service->getFeeScheduleStudentId($this->school, 'stu-1');
        $this->assertSame([40000.0, 24000.0, 16000.0], $current->pluck('amount')->all());
        $this->assertSame('completed', $current[0]['status']);
    }

    public function test_slots_are_stored_as_percentages_and_listed_with_the_specialtys_amount(): void
    {
        Bus::fake();
        Event::fake();
        $service = app(TuitionFeeScheduleSlotService::class);
        $school = new \App\Models\Schoolbranches(['id' => 'branch-1']);
        $school->id = 'branch-1';

        $service->createFeeScheduleSlots($school, 'sched-swe-2', [
            ['due_date' => '2026-12-01', 'fee_percentage' => 25, 'amount' => 999, 'installment_id' => 'inst-1'],
        ], $admin = (object) ['id' => 'admin-1']);

        $slot = FeeScheduleSlot::where('fee_schedule_id', 'sched-swe-2')->sole();
        $this->assertSame(25.0, $slot->percentage);

        $service->updateFeeScheduleSlots($school, 'sched-swe-2', [['slot_id' => $slot->id, 'fee_percentage' => 40]], $admin);

        $listed = $service->getFeeScheduleSlots('sched-swe-2', $school)->sole();
        $this->assertSame(40.0, $listed['fee_percentage']);
        $this->assertSame(40000.0, $listed['amount']);
    }
}
