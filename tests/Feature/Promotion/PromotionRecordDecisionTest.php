<?php

namespace Tests\Feature\Promotion;

use App\Models\Promotion\StudentPromotionRecord;
use App\Models\Schooladmin;
use App\Services\Promotion\PromotionRunLogStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PromotionRecordDecisionTest extends TestCase
{
    use RefreshDatabase;

    private array $studentLogs = [];

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();

        $this->app->instance(PromotionRunLogStore::class, new class($this->studentLogs) extends PromotionRunLogStore {
            public function __construct(private array &$logs) {}
            public function getStudent(string $recordId, string $schoolBranchId): ?array { return $this->logs[$recordId] ?? null; }
        });

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'A', 'name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'a', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Levels', 'key' => 'setting.category.level']);
        DB::table('setting_definations')->insert(['id' => 'def-level', 'name' => 'level.start', 'key' => 'level.start', 'data_type' => 'string', 'setting_category_id' => 'cat']);
        DB::table('school_branch_settings')->insert(['id' => 'set-level', 'school_branch_id' => 'branch-1', 'setting_defination_id' => 'def-level', 'value' => json_encode('level_start_100')]);

        foreach ([['swe-1', 'Software Engineering', 'lvl-1'], ['swe-2', 'Software Engineering', 'lvl-2'], ['net-1', 'Networking', 'lvl-1']] as [$id, $name, $level]) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => $level]);
        }
        DB::table('promotion_runs')->insert(['id' => 'run-done', 'school_year_id' => 'sy-1', 'school_branch_id' => 'branch-1', 'status' => 'completed']);
        DB::table('promotion_runs')->insert(['id' => 'run-busy', 'school_year_id' => 'sy-2', 'school_branch_id' => 'branch-1', 'status' => 'processing']);

        // review-1/2: awaiting a decision; decided: final; no-next: Networking has no Level 200; busy: its run is processing.
        $this->record('review-1', 'swe-1');
        $this->record('review-2', 'swe-1');
        $this->record('decided', 'swe-1', ['outcome' => 'promoted', 'status' => 'completed']);
        $this->record('no-next', 'net-1');
        $this->record('busy', 'swe-1', ['promotion_run_id' => 'run-busy']);
    }

    private function record(string $id, string $specialty, array $overrides = []): void
    {
        DB::table('students')->insert(['id' => "stu-$id", 'name' => "Student $id", 'first_name' => 'S', 'last_name' => $id, 'email' => "$id@example.com", 'password' => 'x', 'username' => $id, 'school_branch_id' => 'branch-1', 'specialty_id' => $specialty, 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        DB::table('student_promotion_records')->insert($overrides + [
            'id' => $id, 'outcome' => 'pending_review', 'status' => 'pending', 'promotion_run_id' => 'run-done',
            'student_id' => "stu-$id", 'from_specialty_id' => $specialty, 'to_specialty_id' => null, 'school_branch_id' => 'branch-1',
        ]);
    }

    private const ALL = ['review-1', 'review-2', 'decided', 'no-next', 'busy'];

    public function test_bulk_promote_promotes_eligible_students_and_explains_each_skip(): void
    {
        $response = $this->postJson('/api/v1/student-promotion-record/bulk-promote', ['record_ids' => self::ALL, 'admin_note' => 'Board approved'])->assertOk();

        $this->assertSame(['review-1', 'review-2'], array_column($response->json('data.done'), 'id'));
        $skipped = collect($response->json('data.skipped'))->pluck('reason', 'id');
        $this->assertStringContainsString('already decided', $skipped['decided']);
        $this->assertStringContainsString('no Networking specialty at the next level', $skipped['no-next']);
        $this->assertStringContainsString('still evaluating', $skipped['busy']);
        $this->assertSame('2 students promoted, 3 skipped.', $response->json('message'));

        $record = StudentPromotionRecord::find('review-1');
        $this->assertSame(['promoted', 'completed', 'swe-2', 'Board approved', 'admin-1'], [$record->outcome, $record->status, $record->to_specialty_id, $record->admin_note, $record->authorable_id]);
        $this->assertSame('swe-2', DB::table('students')->where('id', 'stu-review-1')->value('specialty_id'), 'The student moves to the next level.');
        $this->assertSame('swe-1', DB::table('students')->where('id', 'stu-busy')->value('specialty_id'));
    }

    public function test_bulk_repeat_keeps_students_in_their_level(): void
    {
        $response = $this->postJson('/api/v1/student-promotion-record/bulk-repeat', ['record_ids' => self::ALL])->assertOk();

        $this->assertSame(['review-1', 'review-2', 'no-next'], array_column($response->json('data.done'), 'id'), 'Repeating needs no next level.');
        $record = StudentPromotionRecord::find('no-next');
        $this->assertSame(['repeated', 'completed', 'net-1'], [$record->outcome, $record->status, $record->to_specialty_id]);
        $this->assertSame('net-1', DB::table('students')->where('id', 'stu-no-next')->value('specialty_id'));
    }

    public function test_nothing_changes_when_no_selected_record_qualifies(): void
    {
        $this->postJson('/api/v1/student-promotion-record/bulk-promote', ['record_ids' => ['decided', 'busy']])
            ->assertStatus(409)
            ->assertJsonPath('errors.title', 'Nothing To Promote');

        $this->assertSame('pending', StudentPromotionRecord::find('busy')->status);
    }

    public function test_single_decisions_follow_the_same_rules(): void
    {
        $this->postJson('/api/v1/student-promotion-record/promote', ['record_id' => 'decided'])->assertStatus(409)->assertJsonPath('errors.title', 'Already Decided');
        $this->postJson('/api/v1/student-promotion-record/promote', ['record_id' => 'no-next'])->assertStatus(422)->assertJsonPath('errors.title', 'Next Level Specialty Missing');
        $this->postJson('/api/v1/student-promotion-record/repeat', ['record_id' => 'review-1', 'admin_note' => 'Low attendance'])->assertOk();

        $this->assertSame(['repeated', 'Low attendance'], [StudentPromotionRecord::find('review-1')->outcome, StudentPromotionRecord::find('review-1')->admin_note]);
    }

    public function test_the_record_list_says_what_each_record_allows(): void
    {
        $records = collect($this->getJson('/api/v1/student-promotion-record')->assertOk()->json('data'))->keyBy('id');

        $this->assertTrue($records['review-1']['available_actions']['promote']['allowed']);
        $this->assertFalse($records['decided']['available_actions']['repeat']['allowed']);
        $this->assertSame('processing', $records['busy']['run_status']);
        $this->assertFalse($records['busy']['available_actions']['promote']['allowed']);
        $this->assertFalse($records['no-next']['available_actions']['promote']['allowed']);
        $this->assertStringContainsString('no Networking specialty', $records['no-next']['available_actions']['promote']['reason']);
        $this->assertTrue($records['no-next']['available_actions']['repeat']['allowed']);
    }

    public function test_the_record_log_lists_each_rule_with_failures_first(): void
    {
        $this->studentLogs['review-1'] = [
            ['rule' => 'gpa', 'passed' => true, 'needs_review' => false, 'reasons' => [['reason' => 'GPA 3.1 meets the minimum of 2.0.', 'details' => []]]],
            ['rule' => 'incomplete_exam_scores', 'passed' => false, 'needs_review' => true, 'reasons' => [['reason' => 'Missing CA scores in 2 courses.', 'details' => []]]],
            ['rule' => 'tuition_fee', 'passed' => false, 'needs_review' => false, 'reasons' => [['reason' => 'Owes 50,000 in tuition.', 'details' => []]]],
        ];

        $log = $this->getJson('/api/v1/student-promotion-record/review-1/log')->assertOk()->json('data');

        $this->assertSame(['tuition_fee', 'incomplete_exam_scores', 'gpa'], array_column($log['rules'], 'rule'));
        $this->assertSame(['failed', 'needs_review', 'passed'], array_column($log['rules'], 'result'));
        $this->assertSame('Tuition fee requirement', $log['rules'][0]['title']);
        $this->assertSame(['Owes 50,000 in tuition.'], $log['rules'][0]['reasons']);
        $this->assertSame(['passed' => 1, 'failed' => 1, 'needs_review' => 1], $log['counts']);
        $this->assertSame('Did not meet 1 of 3 promotion rules.', $log['summary']);

        $this->getJson('/api/v1/student-promotion-record/review-2/log')->assertNotFound()->assertJsonPath('errors.title', 'No Log Yet');
    }
}
