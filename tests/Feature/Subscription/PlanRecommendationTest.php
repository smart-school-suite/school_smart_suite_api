<?php

namespace Tests\Feature\Subscription;

use App\Services\SubscriptionPlan\PlanRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlanRecommendationTest extends TestCase
{
    use RefreshDatabase;

    private const COUNTRY = 'country-cm';
    private const BRANCH = 'branch-1';

    /** plan key => [price, courses limit, departments limit] (-1 = unlimited) */
    private const PLANS = [
        'starter'      => [100000, 60, 5],
        'growth'       => [250000, 200, 12],
        'professional' => [500000, 600, 30],
        'ultimate'     => [1000000, -1, -1],
    ];

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('countries')->insert([
            'id' => self::COUNTRY, 'country' => 'Cameroon', 'code' => 'CM',
            'currency' => 'XAF', 'official_language' => 'en',
        ]);
        DB::table('school_branches')->insert(['id' => self::BRANCH, 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);

        foreach (['courses' => 'Courses', 'departments' => 'Departments'] as $key => $name) {
            DB::table('features')->insert([
                'id' => "feature-{$key}", 'key' => "school.{$key}", 'name' => $name, 'description' => "{$name} limit",
                'limit_type' => 'integer', 'default' => '0', 'country_id' => self::COUNTRY, 'status' => 'active',
            ]);
        }

        foreach (self::PLANS as $key => [$price, $courses, $departments]) {
            DB::table('plans')->insert([
                'id' => "plan-{$key}", 'key' => "{$key}.plan", 'name' => ucfirst($key) . ' Plan', 'price' => $price,
                'description' => "{$key} plan", 'country_id' => self::COUNTRY, 'status' => 'active',
                'max_plan' => $key === 'ultimate',
            ]);
            foreach (['courses' => $courses, 'departments' => $departments] as $feature => $limit) {
                DB::table('plan_features')->insert([
                    'id' => "pf-{$key}-{$feature}", 'plan_id' => "plan-{$key}", 'feature_id' => "feature-{$feature}",
                    'country_id' => self::COUNTRY, 'value' => json_encode($limit), 'type' => 'integer', 'default' => '0',
                ]);
            }
        }

        $this->school = (object) ['id' => self::BRANCH, 'country_id' => self::COUNTRY];
    }

    private function subscribe(string $plan, array $usage = [], int $daysLeft = 200): void
    {
        DB::table('school_subscriptions')->insert([
            'id' => 'sub-1', 'school_branch_id' => self::BRANCH, 'plan_id' => "plan-{$plan}", 'country_id' => self::COUNTRY,
            'status' => 'active', 'start_date' => now()->subDays(365 - $daysLeft)->toDateString(),
            'end_date' => now()->addDays($daysLeft)->toDateString(),
        ]);

        foreach ($usage as $feature => $used) {
            DB::table('subscription_usage')->insert([
                'id' => "usage-{$feature}", 'subscription_id' => 'sub-1', 'feature_plan_id' => "pf-{$plan}-{$feature}",
                'school_branch_id' => self::BRANCH, 'limit' => '0', 'current_usage' => json_encode($used), 'limit_type' => 'integer',
            ]);
        }
    }

    private function insights(): array
    {
        return app(PlanRecommendationService::class)->getPlanInsights($this->school);
    }

    public function test_it_recommends_the_cheapest_plan_with_room_for_the_feature_near_its_limit(): void
    {
        $this->subscribe('starter', ['courses' => 55, 'departments' => 1]);

        $insights = $this->insights();
        $rec = $insights['recommendation'];

        $this->assertSame('upgrade', $rec['type']);
        $this->assertSame('plan-growth', $rec['plan_id']);
        $this->assertSame('school.courses', $rec['trigger_feature']['key']);
        $this->assertSame(200, $rec['reasons'][0]['recommended_limit']);
        $this->assertSame('critical', $insights['usage'][0]['status']);
        $this->assertSame(1, $insights['usage_summary']['at_risk']);

        $plans = collect($insights['plans'])->keyBy('id');
        $this->assertTrue($plans['plan-growth']['is_recommended']);
        $this->assertTrue($plans['plan-starter']['is_current']);
        $this->assertSame('upgrade', $plans['plan-professional']['action']);
        $courses = collect($plans['plan-growth']['features'])->firstWhere('key', 'school.courses');
        $this->assertSame('up', $courses['change']);
        $this->assertEquals(3.3, $courses['multiplier']);
    }

    public function test_it_skips_plans_that_would_leave_the_school_near_the_limit_again(): void
    {
        // 180 courses need a limit of 225 to sit at 80%: growth (200) is too small
        $this->subscribe('starter', ['courses' => 180]);

        $this->assertSame('plan-professional', $this->insights()['recommendation']['plan_id']);
    }

    public function test_it_says_the_plan_fits_when_no_feature_is_under_pressure(): void
    {
        $this->subscribe('growth', ['courses' => 20]);

        $insights = $this->insights();

        $this->assertSame('stay', $insights['recommendation']['type']);
        $this->assertSame('plan-growth', $insights['recommendation']['plan_id']);
        $this->assertFalse(collect($insights['plans'])->contains('is_recommended', true));
    }

    public function test_it_advises_renewing_when_the_period_ends_soon(): void
    {
        $this->subscribe('growth', [], daysLeft: 12);

        $insights = $this->insights();

        $this->assertSame('renew', $insights['recommendation']['type']);
        $this->assertSame(12, $insights['subscription']['days_left']);
        $this->assertTrue($insights['subscription']['renewal_due']);
    }

    public function test_the_top_plan_points_to_sales_and_unlimited_features_never_trigger(): void
    {
        $this->subscribe('ultimate', ['courses' => 5000]);

        $insights = $this->insights();

        $this->assertSame('stay', $insights['recommendation']['type']);
        $this->assertSame('unlimited', collect($insights['usage'])->firstWhere('key', 'school.courses')['status']);

        DB::table('plan_features')->where('id', 'pf-ultimate-departments')->update(['value' => json_encode(10)]);
        DB::table('subscription_usage')->insert([
            'id' => 'usage-dep', 'subscription_id' => 'sub-1', 'feature_plan_id' => 'pf-ultimate-departments',
            'school_branch_id' => self::BRANCH, 'limit' => '0', 'current_usage' => '10', 'limit_type' => 'integer',
        ]);

        $this->assertSame('top_plan', $this->insights()['recommendation']['type']);
    }

    public function test_a_rule_can_trigger_earlier_and_supply_its_copy(): void
    {
        $this->subscribe('starter', ['departments' => 3]); // 60%, under the default 70%

        $this->assertSame('stay', $this->insights()['recommendation']['type']);

        DB::table('plan_recs')->insert([
            'id' => 'rec-1', 'priority' => 9, 'status' => 'active', 'source_plan_id' => 'plan-starter',
            'target_plan_id' => 'plan-growth', 'feature_id' => 'feature-departments',
        ]);
        DB::table('plan_rec_conds')->insert([
            'id' => 'cond-1', 'plan_rec_id' => 'rec-1', 'operator' => 'percentage', 'value' => json_encode(['threshold' => 0.5]),
        ]);
        DB::table('plan_rec_copies')->insert([
            'id' => 'copy-1', 'plan_rec_cond_id' => 'cond-1', 'title' => 'More departments ahead',
            'description' => 'Growth gives you 12 departments.', 'cta_text' => 'Get Growth',
        ]);

        $rec = $this->insights()['recommendation'];

        $this->assertSame('upgrade', $rec['type']);
        $this->assertSame('plan-growth', $rec['plan_id']);
        $this->assertSame('More departments ahead', $rec['headline']);
        $this->assertSame('Get Growth', $rec['cta_text']);
        $this->assertSame(9, $rec['priority']);
    }

    public function test_a_school_without_a_subscription_is_pointed_to_the_most_popular_plan(): void
    {
        $insights = $this->insights();

        $this->assertNull($insights['subscription']);
        $this->assertSame('start', $insights['recommendation']['type']);
        $this->assertSame('plan-growth', $insights['recommendation']['plan_id']);
        $this->assertTrue(collect($insights['plans'])->firstWhere('id', 'plan-growth')['is_popular']);
    }
}
