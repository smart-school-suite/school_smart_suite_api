<?php

namespace Tests\Feature\SchoolBranch;

use App\Models\PaymentMethod;
use App\Models\SchoolBranchSetting;
use App\Models\SchoolSubscription;
use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use App\Services\Subscription\SchoolSubscriptionService;
use Database\Seeders\BranchSubscriptionSeeder;
use Database\Seeders\LevelSeeder;
use Database\Seeders\LevelTypeSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingCategorySeeder;
use Database\Seeders\SettingDefinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateSchoolBranchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seed([LevelTypeSeeder::class, LevelSeeder::class, SettingCategorySeeder::class, SettingDefinationSeeder::class, RoleSeeder::class]);

        $this->row('countries', ['id' => 'cm', 'country' => 'Cameroon', 'code' => 'CM', 'currency' => 'XAF', 'official_language' => 'en', 'status' => true]);
        $this->row('countries', ['id' => 'ng', 'country' => 'Nigeria', 'code' => 'NG', 'currency' => 'NGN', 'official_language' => 'en', 'status' => true]);
        $this->row('schools', ['id' => 'school-1', 'name' => 'Bright Future', 'type' => 'private', 'country_id' => 'cm']);
        $this->row('school_branches', ['id' => 'main', 'name' => 'Main Campus', 'abbreviation' => 'MC', 'school_id' => 'school-1']);
        $this->row('payment_method_categories', ['id' => 'mm', 'name' => 'Mobile Money', 'status' => 'active', 'description' => '-']);
        $this->row('payment_method', ['id' => 'mtn', 'name' => 'MTN', 'key' => 'mtn_mobile_money', 'max_deposit' => 1000000, 'max_withdraw' => 1000000, 'status' => 'active', 'description' => '-', 'country_id' => 'cm', 'payment_method_category_id' => 'mm', 'category_id' => 'mm']);
        foreach (['basic' => 50000, 'pro' => 90000] as $key => $price) {
            $this->row('plans', ['id' => $key, 'key' => $key, 'name' => ucfirst($key), 'price' => $price, 'status' => 'active', 'description' => '-', 'country_id' => 'cm']);
        }
        $this->row('plans', ['id' => 'naija', 'key' => 'naija', 'name' => 'Naija', 'price' => 1, 'status' => 'active', 'description' => '-', 'country_id' => 'ng']);
        $this->row('school_admins', ['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'B', 'name' => 'A B', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'ab', 'school_branch_id' => 'main']);

        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');
    }

    private function row(string $table, array $values): void
    {
        DB::table($table)->insert(array_intersect_key($values, array_flip(Schema::getColumnListing($table))));
    }

    private function subscribeMain(string $end = '+200 days'): SchoolSubscription
    {
        return SchoolSubscription::create(['school_branch_id' => 'main', 'plan_id' => 'basic', 'country_id' => 'cm', 'status' => 'active', 'start_date' => now()->subMonth(), 'end_date' => now()->modify($end)]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Town Campus',
            'abbreviation' => 'TC',
            'city' => 'Buea',
            'admin_first_name' => 'Ada',
            'admin_last_name' => 'Lovelace',
            'admin_email' => 'ada@example.com',
            'admin_password' => 'secret123',
            'subscription' => 'shared',
        ], $overrides);
    }

    private function create(array $overrides = [])
    {
        return $this->postJson('/api/v1/school-branch/create', $this->payload($overrides));
    }

    public function test_a_branch_can_share_the_current_subscription(): void
    {
        $source = $this->subscribeMain();

        $this->create()->assertCreated()
            ->assertJsonPath('data.subscription.mode', 'shared')
            ->assertJsonPath('data.subscription.plan', 'Basic');

        $branch = Schoolbranches::where('name', 'Town Campus')->sole();
        $copy = SchoolSubscription::where('school_branch_id', $branch->id)->sole();
        $admin = Schooladmin::where('email', 'ada@example.com')->sole();

        $this->assertSame('school-1', $branch->school_id);
        $this->assertSame($source->id, $copy->shared_from_id);
        $this->assertSame(substr($source->end_date, 0, 10), substr($copy->end_date, 0, 10));
        $this->assertSame($branch->id, $admin->school_branch_id);
        $this->assertTrue($admin->hasRole('schoolSuperAdmin'));
        $this->assertSame(DB::table('setting_definations')->count(), SchoolBranchSetting::where('school_branch_id', $branch->id)->count());
        $this->assertSame(0, DB::table('school_transactions')->where('school_branch_id', $branch->id)->count());
    }

    public function test_a_branch_can_take_its_own_plan(): void
    {
        $this->create(['subscription' => 'new', 'plan_id' => 'pro', 'payment_method_id' => 'mtn'])
            ->assertCreated()
            ->assertJsonPath('data.subscription.mode', 'new')
            ->assertJsonPath('data.subscription.plan', 'Pro');

        $branch = Schoolbranches::where('name', 'Town Campus')->sole();
        $subscription = SchoolSubscription::where('school_branch_id', $branch->id)->sole();

        $this->assertNull($subscription->shared_from_id);
        $this->assertSame('pro', $subscription->plan_id);
        $this->assertSame(1, DB::table('school_transactions')->where('school_branch_id', $branch->id)->where('type', 'subscription_purchase')->count());
    }

    public function test_refusals_leave_nothing_behind(): void
    {
        $this->create()->assertStatus(422)->assertJsonPath('errors.title', 'Nothing to Share');

        $this->subscribeMain();
        $this->create(['name' => ' main campus '])->assertStatus(422)->assertJsonPath('errors.title', 'Branch Already Exists');
        $this->create(['subscription' => 'new', 'plan_id' => 'naija', 'payment_method_id' => 'mtn'])->assertStatus(422)->assertJsonPath('errors.title', 'Plan Not Available');
        $this->create(['subscription' => 'new'])->assertStatus(422)->assertJsonValidationErrors(['plan_id', 'payment_method_id']);
        $this->create(['admin_email' => 'a@example.com'])->assertStatus(422)->assertJsonValidationErrors(['admin_email']);

        $this->assertSame(1, Schoolbranches::count());
        $this->assertSame(1, Schooladmin::count());
    }

    public function test_renewing_or_upgrading_the_owner_carries_the_shared_branch_along(): void
    {
        $source = $this->subscribeMain('+10 days');
        $this->create()->assertCreated();
        $branch = Schoolbranches::where('name', 'Town Campus')->sole();
        $service = app(SchoolSubscriptionService::class);

        $service->renewSubscription(['payment_method_id' => 'mtn'], (object) ['id' => 'main']);
        $copy = SchoolSubscription::where('school_branch_id', $branch->id)->sole();
        $this->assertSame(substr($source->fresh()->end_date, 0, 10), substr($copy->end_date, 0, 10));

        $upgrade = $service->upgradePlan(['new_plan_id' => 'pro', 'payment_method_id' => 'mtn'], (object) ['id' => 'main']);
        $copy->refresh();
        $this->assertSame($upgrade['subscription']->id, $copy->shared_from_id);
        $this->assertSame('pro', $copy->plan_id);

        $this->expectExceptionMessage('SHARED_SUBSCRIPTION');
        $service->renewSubscription(['payment_method_id' => 'mtn'], (object) ['id' => $branch->id]);
    }

    public function test_the_seeder_links_branches_without_a_subscription_once(): void
    {
        $source = $this->subscribeMain();
        $this->row('school_branches', ['id' => 'old', 'name' => 'Old Campus', 'abbreviation' => 'OC', 'school_id' => 'school-1']);
        $this->row('schools', ['id' => 'school-2', 'name' => 'Other', 'type' => 'private', 'country_id' => 'cm']);
        $this->row('school_branches', ['id' => 'elsewhere', 'name' => 'Elsewhere', 'abbreviation' => 'E', 'school_id' => 'school-2']);

        $this->seed(BranchSubscriptionSeeder::class);
        $this->seed(BranchSubscriptionSeeder::class);

        $this->assertSame($source->id, SchoolSubscription::where('school_branch_id', 'old')->sole()->shared_from_id);
        $this->assertSame(0, SchoolSubscription::where('school_branch_id', 'elsewhere')->count());
    }

    public function test_the_options_describe_what_can_be_shared(): void
    {
        $this->getJson('/api/v1/school-branch/creation-options')
            ->assertOk()
            ->assertJsonPath('data.shared.available', false)
            ->assertJsonPath('data.school.currency', 'XAF');

        $this->subscribeMain();
        $this->create()->assertCreated();

        $this->getJson('/api/v1/school-branch/creation-options')
            ->assertOk()
            ->assertJsonPath('data.shared.plan.name', 'Basic')
            ->assertJsonPath('data.shared.owner', 'Main Campus')
            ->assertJsonPath('data.branch_names', ['Main Campus', 'Town Campus'])
            ->assertJsonPath('data.shared.branches', ['Main Campus', 'Town Campus']);
    }
}
