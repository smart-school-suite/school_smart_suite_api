<?php

namespace Tests\Feature\Fees;

use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;
use App\Exceptions\AppException;
use App\Http\Resources\TuitionFeeResource;
use App\Models\TuitionFees;
use App\Models\TuitionFeeTransactions;
use App\Services\TuitionFee\TuitionFeePaymentService;
use App\Services\TuitionFee\TuitionFeeTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Tuition fee transactions are the single source of truth for what a student paid. */
class TuitionFeeLedgerTest extends TestCase
{
    use RefreshDatabase;

    private object $school;
    private object $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        Notification::fake();
        Event::fake([AdminActionEvent::class, StudentActionEvent::class]);
        $this->school = (object) ['id' => 'branch-1'];
        $this->admin = (object) ['id' => 'admin-1'];

        DB::table('specialties')->insert(['id' => 'swe-1', 'specialty_name' => 'SWE', 'registration_fee' => 0, 'school_fee' => 100000, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        DB::table('students')->insert(['id' => 'stu-1', 'name' => 'S', 'first_name' => 'S', 'last_name' => 'S', 'email' => 's@example.com', 'password' => 'x', 'username' => 's', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        foreach (['fee-1' => 100000, 'fee-2' => 50000] as $id => $total) {
            DB::table('tuition_fees')->insert(['id' => $id, 'student_id' => 'stu-1', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'tution_fee_total' => $total]);
        }
    }

    private function pay(float $amount, string $feeId = 'fee-1'): TuitionFees
    {
        return app(TuitionFeePaymentService::class)->payStudentFees(
            ['tuition_id' => $feeId, 'amount' => $amount, 'payment_method' => 'cash'],
            $this->school,
            $this->admin,
        );
    }

    public function test_the_tuition_fee_table_no_longer_stores_what_was_paid(): void
    {
        $this->assertFalse(Schema::hasColumn('tuition_fees', 'amount_paid'));
    }

    public function test_payments_are_recorded_only_as_transactions_and_the_balance_follows(): void
    {
        $this->pay(30000);
        $fee = $this->pay(20000);

        $this->assertSame(2, TuitionFeeTransactions::where('tuition_id', 'fee-1')->count());
        $this->assertSame(50000.0, $fee->amount_paid);
        $this->assertSame(50000.0, $fee->amount_left);
        $this->assertSame('owing', $fee->payment_status);
    }

    public function test_an_overpayment_is_refused_against_the_transaction_total(): void
    {
        $this->pay(90000);

        try {
            $this->pay(10000.01);
            $this->fail('The overpayment was accepted.');
        } catch (AppException $e) {
            $this->assertSame(409, $e->getCode());
        }

        $fee = $this->pay(10000);
        $this->assertSame('completed', $fee->payment_status);
    }

    public function test_reversing_a_payment_removes_its_transaction_and_restores_the_balance(): void
    {
        $this->pay(40000);
        $this->pay(10000);
        $transaction = TuitionFeeTransactions::where('amount', 40000)->sole();

        app(TuitionFeeTransactionService::class)->reverseFeePaymentTransaction($transaction->id, $this->school, $this->admin);

        $this->assertSame(10000.0, TuitionFees::find('fee-1')->amount_paid);
    }

    public function test_bulk_reversal_removes_every_selected_transaction(): void
    {
        $this->pay(40000);
        $this->pay(5000, 'fee-2');
        $ids = TuitionFeeTransactions::pluck('id')->map(fn($id) => ['transaction_id' => $id])->all();

        app(TuitionFeeTransactionService::class)->bulkReverseTuitionFeeTransaction($ids, $this->school, $this->admin);

        $this->assertSame(0, TuitionFeeTransactions::count());
        $this->assertSame(100000.0, TuitionFees::find('fee-1')->amount_left);
    }

    public function test_derived_amounts_agree_however_the_record_is_loaded(): void
    {
        $this->pay(25000);

        $scoped = TuitionFees::withAmountPaid()->orderBy('id')->get();
        $this->assertSame([25000.0, 0.0], $scoped->pluck('amount_paid')->all(), 'A record with no transactions has paid 0.');
        $this->assertSame(25000.0, TuitionFees::with('tuitionFeeTransactions')->find('fee-1')->amount_paid);
        $this->assertSame(25000.0, TuitionFees::find('fee-1')->amount_paid);

        $this->pay(50000, 'fee-2');
        $this->assertSame(['fee-1'], TuitionFees::owing()->pluck('id')->all());

        $resource = (new TuitionFeeResource($scoped->first()))->resolve();
        $this->assertSame([25000.0, 75000.0, 100000.0, 'owing'], [$resource['amount_paid'], $resource['amount_left'], $resource['tuition_fee_total'], $resource['status']]);
    }

    public function test_the_details_payload_carries_the_derived_amounts(): void
    {
        $this->pay(30000);

        $details = app(\App\Services\TuitionFee\TuitionFeeService::class)->getTuitionFeeDetails($this->school, 'fee-1')->toArray();

        $this->assertSame([30000.0, 70000.0, 'owing'], [$details['amount_paid'], $details['amount_left'], $details['payment_status']]);
        $this->assertSame('SWE', $details['specialty']['specialty_name']);
    }

    public function test_the_migration_keeps_amounts_recorded_only_in_the_dropped_column(): void
    {
        $migration = require database_path('migrations/07_constraints/finance/2026_11_17_090000_drop_amount_paid_from_tuition_fees.php');
        $migration->down();
        DB::table('tuition_fees')->where('id', 'fee-1')->update(['amount_paid' => 60000]);
        DB::table('tuition_fees')->where('id', 'fee-2')->update(['amount_paid' => 5000]);
        DB::table('tuition_fee_transactions')->insert(['id' => 't-1', 'transaction_id' => 'TXN-1', 'amount' => 20000, 'payment_method' => 'cash', 'tuition_id' => 'fee-1', 'school_branch_id' => 'branch-1']);
        DB::table('tuition_fee_transactions')->insert(['id' => 't-2', 'transaction_id' => 'TXN-2', 'amount' => 8000, 'payment_method' => 'cash', 'tuition_id' => 'fee-2', 'school_branch_id' => 'branch-1']);

        $migration->up();

        $this->assertFalse(Schema::hasColumn('tuition_fees', 'amount_paid'));
        $balancing = TuitionFeeTransactions::where('transaction_id', 'like', 'BAL-%')->sole();
        $this->assertSame(['fee-1', 40000.0], [$balancing->tuition_id, (float) $balancing->amount]);
        $this->assertSame(60000.0, TuitionFees::find('fee-1')->amount_paid, 'What the student had paid is unchanged.');
        $this->assertSame(8000.0, TuitionFees::find('fee-2')->amount_paid, 'Transactions win when they exceed the column.');
    }
}
