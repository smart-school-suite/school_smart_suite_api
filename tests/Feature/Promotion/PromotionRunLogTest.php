<?php

namespace Tests\Feature\Promotion;

use App\DTOs\Promotion\StudentRuleLogDTO;
use App\DTOs\Promotion\StudentRuleLogReasonDTO;
use App\Jobs\Promotion\StudentPromotionJob;
use App\Models\Promotion\PromotionRun;
use App\Services\Promotion\PromotionRunLogBuilder;
use App\Services\Promotion\PromotionRunLogStore;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PromotionRunLogTest extends TestCase
{
    use RefreshDatabase;

    /** Logs written by the job, keyed by run id (the real store is MongoDB). */
    private array $logs = [];

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-18 10:00:00');
        Bus::fake();

        $this->app->instance(PromotionRunLogStore::class, new class($this->logs) extends PromotionRunLogStore {
            public function __construct(private array &$logs) {}
            public function put(PromotionRun $run, array $log): void { $this->logs[$run->id] = $log; }
            public function putStudent(string $recordId, string $studentId, string $schoolBranchId, array $ruleLogs): void { $this->logs['students'][$studentId] = $ruleLogs; }
        });

        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        DB::table('system_academic_years')->insert(['id' => 'sys', 'name' => '2025/2026', 'year_start' => 2025, 'year_end' => 2026]);
        DB::table('specialties')->insert(['id' => 'swe-1', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        DB::table('school_academic_years')->insert(['id' => 'sy-1', 'start_date' => '2025-09-01', 'end_date' => '2026-07-31', 'specialty_id' => 'swe-1', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);
        DB::table('promotion_runs')->insert(['id' => 'run-1', 'school_year_id' => 'sy-1', 'school_branch_id' => 'branch-1', 'status' => 'processing']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function levelSystem(): void
    {
        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Levels', 'key' => 'setting.category.level']);
        DB::table('setting_definations')->insert(['id' => 'def-level', 'name' => 'level.start', 'key' => 'level.start', 'data_type' => 'string', 'setting_category_id' => 'cat']);
        DB::table('school_branch_settings')->insert(['id' => 'set-level', 'school_branch_id' => 'branch-1', 'setting_defination_id' => 'def-level', 'value' => json_encode('level_start_100')]);
    }

    private function students(int $count): void
    {
        foreach (range(1, $count) as $i) {
            DB::table('students')->insert(['id' => "stu-$i", 'name' => "Student $i", 'first_name' => 'S', 'last_name' => "$i", 'email' => "s$i@example.com", 'password' => 'x', 'username' => "s$i", 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        }
    }

    private function runJob(): array
    {
        (new StudentPromotionJob('run-1', 'branch-1'))->handle();
        return $this->logs['run-1'];
    }

    public function test_every_problem_that_stops_a_run_is_logged_not_just_the_first(): void
    {
        $log = $this->runJob();

        $this->assertSame('failed', $log['status']);
        $this->assertSame(['No students to promote', 'Level setup missing'], array_column($log['issues'], 'title'));
        $this->assertSame(['error', 'error'], array_column($log['issues'], 'severity'));
        $this->assertSame('The run could not start because of 2 problems.', $log['summary']);
        $this->assertSame('failed', PromotionRun::find('run-1')->status);
    }

    public function test_a_missing_next_level_specialty_and_a_running_next_year_are_explained(): void
    {
        $this->levelSystem();
        $this->students(1);

        $log = $this->runJob();
        $this->assertSame(['Next level specialty missing'], array_column($log['issues'], 'title'));
        $this->assertStringContainsString('no Software Engineering specialty at Level 200', $log['issues'][0]['message']);

        DB::table('specialties')->insert(['id' => 'swe-2', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-2']);
        DB::table('school_academic_years')->insert(['id' => 'sy-2', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'swe-2', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);
        DB::table('promotion_runs')->where('id', 'run-1')->update(['status' => 'processing']);

        $log = $this->runJob();
        $this->assertSame(['Next level is already running'], array_column($log['issues'], 'title'));
        $this->assertStringContainsString('until 31 Jul 2027', $log['issues'][0]['message']);
    }

    public function test_a_completed_run_reports_counts_shared_problems_once_and_each_students_reasons(): void
    {
        $this->levelSystem();
        $this->students(3);
        DB::table('specialties')->insert(['id' => 'swe-2', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-2']);

        $log = $this->runJob();

        $this->assertSame('completed', $log['status']);
        $this->assertSame(3, $log['counts']['total']);
        $this->assertSame(3, $log['counts']['processed']);
        $this->assertSame(3, $log['counts']['needs_review'], 'No promotion settings are configured, so every student needs review.');

        $shared = collect($log['issues'])->where('severity', 'warning');
        $this->assertNotEmpty($shared, 'Problems shared by every student are reported once.');
        $this->assertTrue($shared->every(fn($issue) => $issue['details']['affected_students'] === 3));
        $this->assertSame(['Student 1', 'Student 2', 'Student 3'], array_column($log['students'], 'name'));
        $this->assertSame([[], [], []], array_column($log['students'], 'problems'), 'Shared problems are not repeated per student.');

        $run = PromotionRun::find('run-1');
        $this->assertSame([3, 3, 3, 'completed'], [$run->total_students, $run->processed_students, $run->review_count, $run->status]);
    }

    public function test_the_builder_keeps_each_students_own_problems_and_hides_clean_promotions(): void
    {
        $rule = fn(string $name, bool $passed, string $message, bool $review = false) =>
            new StudentRuleLogDTO(rule: $name, passed: $passed, reasons: [new StudentRuleLogReasonDTO(reason: $message)], needsReview: $review);

        $log = (new PromotionRunLogBuilder())
            ->student('a', 'Ada', 'promoted', [$rule('gpa', true, 'GPA 3.2 meets 2.0')])
            ->student('b', 'Ben', 'retained', [$rule('gpa', false, 'GPA 1.4 is below 2.0'), $rule('tuition_fee', false, 'Owes 50,000')])
            ->student('c', 'Cy', 'pending_review', [$rule('incomplete_exam_scores', false, 'Missing CA scores', true)])
            ->build('completed', 'Promotion run completed', ['total' => 3, 'processed' => 3, 'promoted' => 1, 'retained' => 1, 'needs_review' => 1]);

        $this->assertSame(['Ben', 'Cy'], array_column($log['students'], 'name'));
        $this->assertSame(['GPA 1.4 is below 2.0', 'Owes 50,000'], array_column($log['students'][0]['problems'], 'message'));
        $this->assertTrue($log['students'][1]['problems'][0]['needs_review']);
        $this->assertSame([], $log['issues']);
        $this->assertSame('3 of 3 students evaluated: 1 promoted, 1 retained, 1 need review.', $log['summary']);
    }

    public function test_logs_in_the_old_shape_are_converted(): void
    {
        $log = PromotionRunLogBuilder::normalize([
            'status' => 'failed',
            'title' => 'Next Specialty Missing',
            'reasons' => [['reason' => 'Target next specialty could not be determined.', 'details' => ['x' => 1]]],
        ]);

        $this->assertSame(PromotionRunLogBuilder::VERSION, $log['version']);
        $this->assertSame('failed', $log['status']);
        $this->assertSame([['severity' => 'error', 'title' => 'Next Specialty Missing', 'message' => 'Target next specialty could not be determined.', 'details' => ['x' => 1]]], $log['issues']);
    }
}
