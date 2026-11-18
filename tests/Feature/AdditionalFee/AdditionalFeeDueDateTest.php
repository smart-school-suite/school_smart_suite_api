<?php

namespace Tests\Feature\AdditionalFee;

use App\Exceptions\AppException;
use App\Models\AdditionalFees;
use App\Models\SchoolBranchSetting;
use App\Models\Schooladmin;
use App\SchoolBranchSetting\Handlers\AdditionalFee\DefaultDueDaysHandler;
use App\Services\AdditionalFee\AdditionalFeeDueDate;
use Carbon\Carbon;
use Database\Seeders\SettingCategorySeeder;
use Database\Seeders\SettingDefinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** "Default Due Period (Days)" (additionalFee.default_due_days) under Student Additional Fee Settings. */
class AdditionalFeeDueDateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-18 15:30:00');

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('specialties')->insert(['id' => 'swe-1', 'specialty_name' => 'SWE', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        DB::table('additional_fee_categories')->insert(['id' => 'cat-1', 'title' => 'Lab', 'school_branch_id' => 'branch-1']);
        DB::table('students')->insert(['id' => 'stu-1', 'name' => 'S', 'first_name' => 'S', 'last_name' => 'S', 'email' => 's@example.com', 'password' => 'x', 'username' => 's', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'A', 'name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'a', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        (new SettingCategorySeeder())->run();
        (new SettingDefinationSeeder())->run();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function setDays(int $days): void
    {
        $definition = DB::table('setting_definations')->where('key', AdditionalFeeDueDate::KEY)->first();
        DB::table('school_branch_settings')->insert(['id' => 'set-due', 'school_branch_id' => 'branch-1', 'setting_defination_id' => $definition->id, 'value' => json_encode($days)]);
    }

    private function create(array $overrides = [])
    {
        return $this->postJson('/api/v1/additional-fees', $overrides + [
            'reason' => 'Lab coat',
            'amount' => 5000,
            'additionalfee_category_id' => 'cat-1',
            'student_id' => 'stu-1',
        ]);
    }

    public function test_the_setting_is_seeded_in_its_own_category(): void
    {
        $definition = DB::table('setting_definations')->where('key', AdditionalFeeDueDate::KEY)->first();

        $this->assertSame('integer', $definition->data_type);
        $this->assertSame(30, (int) $definition->default_value);
        $this->assertSame('Student Additional Fee Settings', DB::table('setting_categories')->where('id', $definition->setting_category_id)->value('name'));
    }

    public function test_a_fee_billed_without_a_due_date_is_due_that_many_days_from_today(): void
    {
        $this->setDays(10);

        $this->create()->assertSuccessful();

        $this->assertSame('2026-11-28', substr((string) AdditionalFees::sole()->due_date, 0, 10));
    }

    public function test_a_due_date_entered_when_billing_overrides_the_default(): void
    {
        $this->setDays(10);

        $this->create(["due_date" => "2026-12-15"])->assertSuccessful();

        $this->assertSame('2026-12-15', substr((string) AdditionalFees::sole()->due_date, 0, 10));
    }

    public function test_zero_days_makes_the_fee_due_today_and_no_setting_uses_the_default(): void
    {
        $this->assertSame('2026-12-18', app(AdditionalFeeDueDate::class)->resolve('branch-1'), 'No branch row: the 30-day default.');

        $this->setDays(0);
        $this->assertSame('2026-11-18', app(AdditionalFeeDueDate::class)->resolve('branch-1'));
    }

    public function test_the_setting_accepts_whole_days_within_range_only(): void
    {
        $this->setDays(10);
        $setting = SchoolBranchSetting::find('set-due');
        $handler = new DefaultDueDaysHandler();

        $this->assertSame(14, (int) $handler->handle($setting, '14', (object) ['id' => 'branch-1'], null)->value);

        foreach (['abc', 2.5, -1, 400] as $invalid) {
            try {
                $handler->handle($setting, $invalid, (object) ['id' => 'branch-1'], null);
                $this->fail('Accepted ' . json_encode($invalid));
            } catch (AppException $e) {
                $this->assertSame('Invalid Due Period', $e->getTitle());
            }
        }
    }
}
