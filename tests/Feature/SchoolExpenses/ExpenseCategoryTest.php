<?php

namespace Tests\Feature\SchoolExpenses;

use App\Exceptions\AppException;
use App\Models\Schooladmin;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpenseCategoryTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/v1/expenses-category';

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('school_branches')->insert([
            ['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN'],
            ['id' => 'branch-2', 'school_id' => 'school-1', 'name' => 'Annex', 'abbreviation' => 'AN'],
        ]);
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'A', 'name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'a', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');
    }

    private function category(string $id, string $name, string $branch = 'branch-1', string $status = 'active', ?string $description = null): void
    {
        DB::table('expense_categories')->insert(['id' => $id, 'name' => $name, 'description' => $description, 'status' => $status, 'school_branch_id' => $branch]);
    }

    private function expense(string $id, string $category): void
    {
        DB::table('school_expenses')->insert(['id' => $id, 'date' => '2026-11-01', 'amount' => 100, 'school_branch_id' => 'branch-1', 'expenses_category_id' => $category]);
    }

    public function test_an_empty_list_is_a_normal_answer(): void
    {
        $this->getJson(self::URL)->assertOk()->assertJsonPath('status', 'success')->assertJsonPath('data', []);
    }

    public function test_the_list_is_the_branchs_categories_by_name_with_their_expense_counts(): void
    {
        $this->category('c-util', 'Utilities');
        $this->category('c-main', 'maintenance');
        $this->category('c-other', 'Annex Only', 'branch-2');
        $this->expense('e1', 'c-util');
        $this->expense('e2', 'c-util');

        $data = $this->getJson(self::URL)->assertOk()->json('data');

        $this->assertSame(['maintenance', 'Utilities'], array_column($data, 'name'), 'Sorted by name; the other branch is never listed.');
        $this->assertSame([0, 2], array_column($data, 'expenses_count'));
    }

    public function test_the_list_filters_by_status_and_search(): void
    {
        $this->category('c-util', 'Utilities', description: 'Water and power');
        $this->category('c-trip', 'Field trips', status: 'inactive');

        $this->assertSame(['Field trips'], array_column($this->getJson(self::URL . '?status=inactive')->json('data'), 'name'));
        $this->assertSame(['Utilities'], array_column($this->getJson(self::URL . '?search=POWER')->json('data'), 'name'), 'Search covers the description, ignoring case.');
        $this->getJson(self::URL . '?status=archived')->assertStatus(422)->assertJsonPath('errors.title', 'Invalid Status Filter');
    }

    public function test_creating_saves_the_description_and_refuses_a_duplicate_name(): void
    {
        $this->category('c-other', 'Utilities', 'branch-2');

        $this->postJson(self::URL, ['name' => '  Utilities  ', 'description' => 'Water and power'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Utilities')
            ->assertJsonPath('data.description', 'Water and power')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.school_branch_id', 'branch-1');
        $id = DB::table('expense_categories')->where('name', 'Utilities')->where('school_branch_id', 'branch-1')->value('id');
        $this->assertTrue(\Illuminate\Support\Str::isUuid($id), 'The new category keeps its UUID.');

        $this->postJson(self::URL, ['name' => 'UTILITIES'])
            ->assertStatus(409)
            ->assertJsonPath('errors.title', 'Category Already Exists');
        $this->assertSame(1, DB::table('expense_categories')->where('school_branch_id', 'branch-1')->count(), 'The same name in another branch is fine.');
    }

    public function test_updating_changes_only_what_is_sent(): void
    {
        $this->category('c-util', 'Utilities', description: 'Water and power');
        $this->category('c-main', 'Maintenance');

        $this->putJson(self::URL . '/c-util', ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Utilities')
            ->assertJsonPath('data.description', 'Water and power')
            ->assertJsonPath('data.status', 'inactive');

        $this->putJson(self::URL . '/c-util', ['description' => null])->assertOk()->assertJsonPath('data.description', null);
        $this->putJson(self::URL . '/c-util', ['name' => 'utilities'])->assertOk()->assertJsonPath('data.name', 'utilities');
        $this->putJson(self::URL . '/c-util', ['name' => 'Maintenance'])->assertStatus(409);
    }

    public function test_a_missing_or_other_branchs_category_is_a_404_not_a_success(): void
    {
        $this->category('c-other', 'Annex Only', 'branch-2');

        foreach (['missing', 'c-other'] as $id) {
            $this->putJson(self::URL . "/{$id}", ['name' => 'X'])->assertNotFound()->assertJsonPath('errors.title', 'Expense Category Not Found');
            $this->deleteJson(self::URL . "/{$id}")->assertNotFound();
        }
        $this->assertSame('Annex Only', DB::table('expense_categories')->where('id', 'c-other')->value('name'));
    }

    public function test_a_category_in_use_cannot_be_deleted(): void
    {
        $this->category('c-util', 'Utilities');
        $this->category('c-unused', 'Unused');
        $this->expense('e1', 'c-util');
        $this->expense('e2', 'c-util');

        $this->deleteJson(self::URL . '/c-util')
            ->assertStatus(409)
            ->assertJsonPath('errors.title', 'Category In Use')
            ->assertJsonPath('errors.description', '2 expenses are filed under "Utilities". Move them to another category first, or mark this category inactive so it is no longer offered for new expenses.');

        $this->deleteJson(self::URL . '/c-unused')->assertOk();
        $this->assertSame(['c-util'], DB::table('expense_categories')->pluck('id')->all());
    }

    public function test_the_migration_gives_categories_their_expenses_branch(): void
    {
        DB::table('expense_categories')->insert([
            ['id' => 'c-used', 'name' => 'Used', 'school_branch_id' => null],
            ['id' => 'c-lone', 'name' => 'Lone', 'school_branch_id' => null],
        ]);
        DB::table('school_expenses')->insert(['id' => 'e1', 'date' => '2026-11-01', 'amount' => 5, 'school_branch_id' => 'branch-2', 'expenses_category_id' => 'c-used']);

        $migration = require database_path('migrations/04_finance/2026_11_21_120000_add_branch_and_status_to_expense_categories.php');
        $migration->up();
        $migration->up();

        $this->assertSame(['c-lone' => null, 'c-used' => 'branch-2'], DB::table('expense_categories')->orderBy('id')->pluck('school_branch_id', 'id')->all());
        $this->assertSame('active', DB::table('expense_categories')->where('id', 'c-used')->value('status'));
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

    public function test_an_unexpected_failure_is_reported_with_its_real_cause(): void
    {
        $reported = $this->reported();
        Schema::rename('expense_categories', 'expense_categories_gone');

        $this->getJson(self::URL)->assertStatus(500)->assertJsonPath('errors.title', 'Something Went Wrong');

        $this->assertCount(1, $reported);
        $this->assertInstanceOf(AppException::class, $reported[0]);
        $cause = $reported[0]->getPrevious();
        $this->assertInstanceOf(QueryException::class, $cause, 'The database error travels with the report.');
        $this->assertStringContainsString('expense_categories', $cause->getMessage());
    }

    public function test_expected_refusals_are_not_reported_as_errors(): void
    {
        $reported = $this->reported();
        $this->category('c-util', 'Utilities');

        $this->postJson(self::URL, ['name' => 'Utilities'])->assertStatus(409);
        $this->deleteJson(self::URL . '/missing')->assertNotFound();

        $this->assertCount(0, $reported, 'A 409 or 404 answer is not a bug, so Sentry should not see it.');
    }
}
