<?php

namespace Tests\Feature\AdditionalFee;

use App\Models\AdditionalFees;
use App\Models\Schooladmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BulkBillAdditionalFeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('specialties')->insert(['id' => 'swe-1', 'specialty_name' => 'SWE', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        DB::table('additional_fee_categories')->insert(['id' => 'cat-1', 'title' => 'Lab', 'school_branch_id' => 'branch-1']);
        foreach (['stu-1', 'stu-2'] as $id) {
            DB::table('students')->insert(['id' => $id, 'name' => $id, 'first_name' => $id, 'last_name' => $id, 'email' => "$id@example.com", 'password' => 'x', 'username' => $id, 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        }

        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'A', 'name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'a', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');
    }

    private function bill(array $overrides = [])
    {
        $row = fn(string $student) => array_merge([
            'student_id' => $student,
            'reason' => 'Laboratory equipment contribution',
            'additionalfee_category_id' => 'cat-1',
            'amount' => 15000,
            'due_date' => now()->addMonth()->toDateString(),
        ], $overrides);

        return $this->postJson('/api/v1/additional-fees/bulk-bill', ['fee_details' => [$row('stu-1'), $row('stu-2')]]);
    }

    public function test_every_selected_student_is_billed(): void
    {
        $this->bill()->assertOk();

        $fees = AdditionalFees::orderBy('student_id')->get();
        $this->assertSame(['stu-1', 'stu-2'], $fees->pluck('student_id')->all());
        $this->assertSame(['unpaid', 'unpaid'], $fees->pluck('status')->all());
        $this->assertSame(['swe-1', 'swe-1'], $fees->pluck('specialty_id')->all());
        $this->assertEquals([15000, 15000], $fees->pluck('amount')->map(fn($v) => (float) $v)->all());
    }

    public function test_a_missing_due_date_defaults_to_the_due_period_setting(): void
    {
        $this->bill(['due_date' => null])->assertOk();

        // No setting row: the default of 30 days from today.
        $this->assertSame([now()->addDays(30)->toDateString()], AdditionalFees::pluck('due_date')->map(fn($d) => substr((string) $d, 0, 10))->unique()->values()->all());
    }

    public function test_a_due_date_in_the_past_is_still_rejected(): void
    {
        $this->bill(['due_date' => now()->subDay()->toDateString()])->assertStatus(422);

        $this->assertSame(0, AdditionalFees::count());
    }
}
