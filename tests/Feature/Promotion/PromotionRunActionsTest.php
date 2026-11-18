<?php

namespace Tests\Feature\Promotion;

use App\Jobs\Promotion\StudentPromotionJob;
use App\Models\Promotion\PromotionRun;
use App\Models\Schooladmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PromotionRunActionsTest extends TestCase
{
    use RefreshDatabase;

    private const STATUSES = ['pending', 'scheduled', 'evaluating', 'processing', 'completed', 'completed_with_issues', 'failed', 'cancelled'];

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake([StudentPromotionJob::class]);

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'A', 'name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'a', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        // One run per status, plus one in another branch.
        foreach (self::STATUSES as $status) {
            DB::table('promotion_runs')->insert(['id' => "run-{$status}", 'school_year_id' => "sy-{$status}", 'school_branch_id' => 'branch-1', 'status' => $status]);
        }
        DB::table('promotion_runs')->insert(['id' => 'run-other-branch', 'school_year_id' => 'sy-x', 'school_branch_id' => 'branch-2', 'status' => 'pending']);
    }

    private function runStatus(string $id): string
    {
        return PromotionRun::find($id)->status;
    }

    public static function allowedByAction(): array
    {
        return [
            'trigger' => ['bulk-trigger', ['pending', 'cancelled']],
            'retry' => ['bulk-retry', ['failed', 'completed_with_issues', 'cancelled']],
            'cancel' => ['bulk-cancel', ['scheduled', 'evaluating', 'processing']],
        ];
    }

    #[DataProvider('allowedByAction')]
    public function test_each_bulk_action_applies_only_to_runs_whose_status_allows_it(string $endpoint, array $allowed): void
    {
        $ids = array_map(fn($status) => "run-{$status}", self::STATUSES);

        $response = $this->postJson("/api/v1/promotion-run/{$endpoint}", ['promotion_run_ids' => $ids, 'status' => 'active'])->assertOk();

        $this->assertEqualsCanonicalizing(array_map(fn($s) => "run-{$s}", $allowed), array_column($response->json('data.done'), 'id'));
        $this->assertCount(count(self::STATUSES) - count($allowed), $response->json('data.skipped'));
        foreach ($response->json('data.skipped') as $skipped) {
            $this->assertNotEmpty($skipped['reason'], "{$skipped['id']} was skipped without a reason.");
        }
    }

    public function test_bulk_trigger_queues_a_job_per_run_and_marks_them_processing(): void
    {
        $this->postJson('/api/v1/promotion-run/bulk-trigger', ['promotion_run_ids' => ['run-pending', 'run-cancelled'], 'status' => 'active'])->assertOk();

        $this->assertSame(['processing', 'processing'], [$this->runStatus('run-pending'), $this->runStatus('run-cancelled')]);
        Bus::assertDispatchedTimes(StudentPromotionJob::class, 2);
    }

    public function test_bulk_trigger_can_schedule_runs_for_later(): void
    {
        $at = now()->addDay()->startOfHour();

        $this->postJson('/api/v1/promotion-run/bulk-trigger', ['promotion_run_ids' => ['run-pending'], 'status' => 'scheduled', 'initiate_at' => $at->toDateTimeString()])->assertOk();

        $this->assertSame('scheduled', $this->runStatus('run-pending'));
        Bus::assertDispatched(StudentPromotionJob::class, fn($job) => $job->delay?->equalTo($at));
        $this->postJson('/api/v1/promotion-run/bulk-trigger', ['promotion_run_ids' => ['run-pending'], 'status' => 'scheduled'])->assertStatus(422);
    }

    public function test_bulk_retry_and_cancel_change_the_runs(): void
    {
        $this->postJson('/api/v1/promotion-run/bulk-retry', ['promotion_run_ids' => ['run-failed', 'run-completed_with_issues']])->assertOk();
        $this->assertSame(['processing', 'processing'], [$this->runStatus('run-failed'), $this->runStatus('run-completed_with_issues')]);

        $this->postJson('/api/v1/promotion-run/bulk-cancel', ['promotion_run_ids' => ['run-scheduled', 'run-processing']])->assertOk();
        $this->assertSame(['cancelled', 'cancelled'], [$this->runStatus('run-scheduled'), $this->runStatus('run-processing')]);
    }

    public function test_a_bulk_action_no_selected_run_allows_is_refused_and_changes_nothing(): void
    {
        $this->postJson('/api/v1/promotion-run/bulk-cancel', ['promotion_run_ids' => ['run-completed', 'run-pending']])
            ->assertStatus(409)
            ->assertJsonPath('errors.title', 'Nothing To Cancel');

        $this->assertSame(['completed', 'pending'], [$this->runStatus('run-completed'), $this->runStatus('run-pending')]);
    }

    public function test_runs_of_another_branch_are_never_touched(): void
    {
        $response = $this->postJson('/api/v1/promotion-run/bulk-trigger', ['promotion_run_ids' => ['run-pending', 'run-other-branch'], 'status' => 'active'])->assertOk();

        $this->assertSame('This run no longer exists.', collect($response->json('data.skipped'))->firstWhere('id', 'run-other-branch')['reason']);
        $this->assertSame('pending', $this->runStatus('run-other-branch'));
    }

    public function test_single_actions_follow_the_same_rules(): void
    {
        $uuid = '7d1f8a2e-4b3c-4d5e-8f60-1a2b3c4d5e6f';
        DB::table('promotion_runs')->insert(['id' => $uuid, 'school_year_id' => 'sy-u', 'school_branch_id' => 'branch-1', 'status' => 'scheduled']);
        $this->postJson('/api/v1/promotion-run/trigger', ['promotion_run_id' => $uuid, 'status' => 'active'])->assertStatus(409);
        $this->postJson('/api/v1/promotion-run/run-pending/retry')->assertStatus(409);
        $this->postJson('/api/v1/promotion-run/run-completed/cancel')->assertStatus(409);

        $this->postJson('/api/v1/promotion-run/run-processing/cancel')->assertOk();
        $this->assertSame('cancelled', $this->runStatus('run-processing'));
    }

    public function test_the_run_list_says_which_actions_each_run_allows(): void
    {
        $runs = collect($this->getJson('/api/v1/promotion-run')->assertOk()->json('data'))->keyBy('id');

        $this->assertTrue($runs['run-pending']['available_actions']['trigger']['allowed']);
        $this->assertFalse($runs['run-pending']['available_actions']['cancel']['allowed']);
        $this->assertSame('This run has not been triggered, so there is nothing to cancel.', $runs['run-pending']['available_actions']['cancel']['reason']);
        $this->assertTrue($runs['run-processing']['available_actions']['cancel']['allowed']);
    }

    public function test_the_job_leaves_a_run_alone_once_it_was_cancelled(): void
    {
        DB::table('promotion_runs')->where('id', 'run-cancelled')->update(['processed_students' => 0]);

        (new StudentPromotionJob('run-cancelled', 'branch-1'))->handle();

        // Returns before loading students or writing logs (a Mongo collection).
        $this->assertSame('cancelled', $this->runStatus('run-cancelled'));
        $this->assertNull(PromotionRun::find('run-cancelled')->started_at);
    }

    public function test_the_details_show_the_run_flat_with_who_started_it_and_what_it_allows(): void
    {
        $this->postJson('/api/v1/promotion-run/bulk-trigger', ['promotion_run_ids' => ['run-pending'], 'status' => 'active'])->assertOk();

        $details = $this->getJson('/api/v1/promotion-run/run-pending')->assertOk()->json('data');

        $this->assertSame(['run-pending', 'processing', 'A'], [$details['id'], $details['status'], $details['initiated_by']]);
        $this->assertNotNull($details['started_at']);
        $this->assertTrue($details['available_actions']['cancel']['allowed']);
        $this->assertFalse($details['available_actions']['trigger']['allowed']);
        $this->getJson('/api/v1/promotion-run/run-other-branch')->assertNotFound();
    }
}
