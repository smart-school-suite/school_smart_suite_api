<?php

namespace Tests\Feature\Subscription;

use Database\Seeders\LevelSeeder;
use Database\Seeders\LevelTypeSeeder;
use Database\Seeders\SettingCategorySeeder;
use Database\Seeders\SettingDefinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchoolSubscribeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->seed([LevelTypeSeeder::class, LevelSeeder::class, SettingCategorySeeder::class, SettingDefinationSeeder::class]);
        $this->row('countries', ['id' => 'cm', 'country' => 'Cameroon', 'code' => 'CM', 'currency' => 'XAF', 'official_language' => 'en', 'status' => true]);
        $this->row('payment_method_categories', ['id' => 'mm', 'name' => 'Mobile Money', 'status' => 'active', 'description' => '-']);
        $this->row('payment_method', ['id' => 'mtn', 'name' => 'MTN', 'key' => 'mtn_mobile_money', 'max_deposit' => 1000000, 'max_withdraw' => 1000000, 'status' => 'active', 'description' => '-', 'country_id' => 'cm', 'payment_method_category_id' => 'mm', 'category_id' => 'mm']);
        $this->row('plans', ['id' => 'plan-1', 'key' => 'basic', 'name' => 'Basic', 'price' => 50000, 'status' => 'active', 'description' => '-', 'country_id' => 'cm']);
    }

    private function row(string $table, array $values): void
    {
        DB::table($table)->insert(array_intersect_key($values, array_flip(Schema::getColumnListing($table))));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'plan_id' => 'plan-1',
            'payment_method_id' => 'mtn',
            'phone_number' => '677000000',
            'promo_code' => '',
            'country_id' => 'cm',
            'type' => 'private',
            'school_branch_name' => 'Main',
            'school_name' => 'Test School',
            'abbreviation' => 'TS',
            'admin_full_name' => 'Ada Admin',
            'admin_last_name' => 'Admin',
            'admin_first_name' => 'Ada',
            'admin_email' => 'ada@example.com',
            'admin_password' => 'secret123',
        ], $overrides);
    }

    public function test_subscribing_creates_the_school_and_returns_a_token(): void
    {
        $this->postJson('/api/v1/school-subscription/subscribe', $this->payload())
            ->assertOk()
            ->assertJsonStructure(['data' => ['authToken']]);

        $branchId = DB::table('school_admins')->where('email', 'ada@example.com')->value('school_branch_id');
        $this->assertNotNull($branchId);
        $this->assertSame(DB::table('setting_definations')->count(), DB::table('school_branch_settings')->where('school_branch_id', $branchId)->count());
        $this->assertSame(1, DB::table('school_subscriptions')->where('school_branch_id', $branchId)->where('status', 'active')->count());
    }

    public function test_a_taken_admin_email_is_refused_with_a_readable_reason(): void
    {
        $this->postJson('/api/v1/school-subscription/subscribe', $this->payload())->assertOk();

        $this->postJson('/api/v1/school-subscription/subscribe', $this->payload(['school_name' => 'Other']))
            ->assertStatus(422)
            ->assertJsonPath('errors.admin_email.0', fn ($message) => str_contains($message, 'taken'));
    }
}
