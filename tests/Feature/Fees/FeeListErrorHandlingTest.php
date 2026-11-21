<?php

namespace Tests\Feature\Fees;

use App\Exceptions\AppException;
use App\Models\Schooladmin;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The fee and fee transaction lists: empty is normal, failures keep their cause. */
class FeeListErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    private const LISTS = [
        'tuition' => ['/api/v1/tuition-fee', 'tuition_fees', 'load the tuition fees'],
        'registration' => ['/api/v1/registration-fees', 'registration_fees', 'load the registration fees'],
        'tuition-transactions' => ['/api/v1/tuition-fee/transaction', 'tuition_fee_transactions', 'load the tuition fee transactions'],
        'registration-transactions' => ['/api/v1/registration-fees/transactions', 'registration_fee_transactions', 'load the registration fee transactions'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('school_branches')->insert([
            ['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN'],
            ['id' => 'branch-2', 'school_id' => 'school-1', 'name' => 'Annex', 'abbreviation' => 'AN'],
        ]);
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'A', 'name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'a', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        foreach (['schoolAdmin.tuitionFee.view', 'schoolAdmin.registrationFee.view'] as $permission) {
            DB::table('permissions')->insert(['uuid' => (string) \Illuminate\Support\Str::uuid(), 'name' => $permission, 'guard_name' => 'schooladmin', 'permission_category_id' => 'fees']);
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $admin->givePermissionTo(['schoolAdmin.tuitionFee.view', 'schoolAdmin.registrationFee.view']);
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        DB::table('levels')->insert(['id' => 'lvl-1', 'name' => 'Level 100', 'level' => 100, 'program_name' => 'l1', 'level_type_id' => 't']);
        DB::table('specialties')->insert(['id' => 'swe', 'specialty_name' => 'SWE', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        DB::table('students')->insert(['id' => 'stu', 'name' => 'Ada Lovelace', 'first_name' => 'Ada', 'last_name' => 'L', 'email' => 'ada@example.com', 'password' => 'x', 'username' => 'ada', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
    }

    private function fee(string $table, string $id, string $branch): void
    {
        $row = ['id' => $id, 'student_id' => 'stu', 'school_branch_id' => $branch, 'specialty_id' => 'swe', 'level_id' => 'lvl-1'];
        $row += match ($table) {
            'tuition_fees' => ['tution_fee_total' => 100000],
            'registration_fees' => ['amount' => 5000],
            default => ['transaction_id' => "TX-$id", 'amount' => 500, 'payment_method' => 'cash', 'tuition_id' => 'fee', 'registrationfee_id' => 'fee'],
        };
        DB::table($table)->insert(array_intersect_key($row, array_flip(Schema::getColumnListing($table))));
    }

    /** What the exception handler reports, seen from after Sentry's callback (so what Sentry gets). */
    private function reported(): \ArrayObject
    {
        $reported = new \ArrayObject();
        app(ExceptionHandler::class)->reportable(function (\Throwable $e) use ($reported) {
            $reported[] = $e;
        });
        return $reported;
    }

    public function test_an_empty_list_is_a_normal_answer(): void
    {
        foreach (self::LISTS as [$url]) {
            $this->getJson($url)->assertOk()->assertJsonPath('data', []);
        }
    }

    public function test_the_list_holds_the_branchs_fees_only(): void
    {
        foreach (self::LISTS as $name => [$url, $table]) {
            $this->fee($table, "$name-mine", 'branch-1');
            $this->fee($table, "$name-other", 'branch-2');

            $this->assertSame(["$name-mine"], array_column($this->getJson($url)->assertOk()->json('data'), 'id'));
        }
    }

    public function test_an_unexpected_failure_is_reported_with_its_real_cause(): void
    {
        foreach (self::LISTS as [$url, $table, $action]) {
            $reported = $this->reported();
            Schema::rename($table, "{$table}_gone");

            $this->getJson($url)
                ->assertStatus(500)
                ->assertJsonPath('errors.title', 'Something Went Wrong')
                ->assertJsonPath('message', "An unexpected error occurred while trying to {$action}.");

            $this->assertCount(1, $reported);
            $this->assertInstanceOf(AppException::class, $reported[0]);
            $this->assertInstanceOf(QueryException::class, $reported[0]->getPrevious(), 'The database error travels with the report.');
            $this->assertStringContainsString($table, $reported[0]->getPrevious()->getMessage());

            Schema::rename("{$table}_gone", $table);
        }
    }
}
