<?php

namespace Tests\Feature\ResitResult;

use App\Models\ResitResultPublication;
use App\Models\Schooladmin;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResitResultManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-18 10:00:00');

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'Ada', 'last_name' => 'Admin', 'name' => 'Ada Admin', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'a', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        DB::table('system_academic_years')->insert(['id' => 'sys', 'name' => '2026/2027', 'year_start' => 2026, 'year_end' => 2027]);
        DB::table('levels')->insert(['id' => 'lvl-2', 'name' => 'Level 200', 'level' => 200, 'program_name' => 'l2', 'level_type_id' => 't']);
        DB::table('specialties')->insert(['id' => 'swe', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-2']);
        DB::table('school_academic_years')->insert(['id' => 'sy', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'swe', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);
        DB::table('semesters')->insert(['id' => 'first', 'name' => 'First Semester', 'count' => 1, 'program_name' => 'HND']);
        DB::table('exam_types')->insert(['id' => 'resit-1', 'exam_name' => 'First Semester Resit', 'semester' => 'first', 'type' => 'resit', 'program_name' => 'r1', 'semester_id' => 'first']);

        // done: both candidates scored; partial: one of two; empty: no candidates.
        foreach (['done', 'partial', 'empty'] as $exam) {
            DB::table('resit_exams')->insert(['id' => $exam, 'start_date' => '2026-11-01', 'end_date' => '2026-11-05', 'max_score' => 100, 'school_year_id' => 'sy', 'exam_type_id' => 'resit-1', 'school_branch_id' => 'branch-1']);
        }
        $this->candidate('done', 'Ben', scored: true);
        $this->candidate('done', 'Cara', scored: true);
        $this->candidate('partial', 'Dan', scored: true);
        $this->candidate('partial', 'Eve', scored: false);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function candidate(string $exam, string $name, bool $scored): void
    {
        $student = "stu-$exam-$name";
        DB::table('students')->insert(['id' => $student, 'name' => $name, 'first_name' => $name, 'last_name' => 'S', 'email' => "$student@example.com", 'password' => 'x', 'username' => strtolower($name), 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        DB::table('resit_candidates')->insert(['id' => "cand-$exam-$name", 'resit_exam_id' => $exam, 'student_id' => $student, 'school_branch_id' => 'branch-1']);
        if ($scored) {
            DB::table('resit_marks')->insert(['id' => "mark-$exam-$name", 'score' => 55, 'candidate_id' => "cand-$exam-$name", 'resit_id' => 'r', 'resit_exam_id' => $exam, 'grade_id' => 'g', 'school_branch_id' => 'branch-1']);
        }
    }

    private function publish(array $payload)
    {
        return $this->postJson('/api/v1/resit-results/publish', $payload);
    }

    public function test_the_list_shows_each_resit_exams_evaluation_and_publication_status(): void
    {
        $exams = collect($this->getJson('/api/v1/resit-results/resit-exams')->assertOk()->json('data'))->keyBy('id');

        $this->assertSame([2, 2, 0], [$exams['done']['total_candidates'], $exams['done']['evaluated_candidates'], $exams['done']['not_evaluated_candidates']]);
        $this->assertSame('ready_to_publish', $exams['done']['publication_status']);
        $this->assertSame('pending_evaluation', $exams['partial']['publication_status']);
        $this->assertSame('pending_evaluation', $exams['empty']['publication_status']);
        $this->assertSame(['First Semester Resit', 'Software Engineering', 'Level 200', '2026/2027', 'finished'], [$exams['done']['exam_name'], $exams['done']['specialty_name'], $exams['done']['level_name'], $exams['done']['school_year'], $exams['done']['status']]);
    }

    public function test_results_publish_now_once_every_candidate_is_evaluated(): void
    {
        $response = $this->publish(['resit_exam_id' => 'done', 'status' => 'active'])->assertOk();

        $this->assertSame('published', $response->json('data.publication_status'));
        $this->assertSame('Ada Admin', $response->json('data.published_by'));
        $this->assertSame('Resit Results Published Successfully', $response->json('message'));

        $this->publish(['resit_exam_id' => 'done', 'status' => 'active'])
            ->assertStatus(409)
            ->assertJsonPath('errors.title', 'Already Published');
    }

    public function test_results_can_be_scheduled_and_rescheduled_before_they_go_out(): void
    {
        $this->publish(['resit_exam_id' => 'done', 'status' => 'scheduled', 'published_at' => '2026-11-20 08:00:00'])
            ->assertOk()
            ->assertJsonPath('data.publication_status', 'scheduled');
        $this->publish(['resit_exam_id' => 'done', 'status' => 'scheduled', 'published_at' => '2026-11-22 08:00:00'])->assertOk();

        $this->assertSame('2026-11-22 08:00:00', ResitResultPublication::sole()->published_at->toDateTimeString());
        $this->publish(['resit_exam_id' => 'done', 'status' => 'scheduled'])->assertStatus(422);
    }

    public function test_results_are_not_published_before_evaluation_is_finished(): void
    {
        $this->publish(['resit_exam_id' => 'partial', 'status' => 'active'])
            ->assertStatus(409)
            ->assertJsonPath('errors.title', 'Not Ready To Publish');
        $this->publish(['resit_exam_id' => 'empty', 'status' => 'active'])
            ->assertStatus(409)
            ->assertJsonPath('errors.title', 'Nothing To Publish');

        $this->assertSame(0, ResitResultPublication::count());
    }

    public function test_the_progress_lists_who_is_and_is_not_evaluated(): void
    {
        $progress = $this->getJson('/api/v1/resit-results/resit-exam/partial/progress')->assertOk()->json('data');

        $this->assertSame(['total_candidates' => 2, 'total_evaluated' => 1, 'total_not_evaluated' => 1, 'evaluated_percentage' => 50, 'not_evaluated_percentage' => 50], $progress['summary']);
        $this->assertSame(['Dan'], array_column($progress['evaluated_students'], 'name'));
        $this->assertSame(['Eve'], array_column($progress['not_evaluated_students'], 'name'));
    }

    public function test_publish_details_before_and_after_publishing(): void
    {
        $this->getJson('/api/v1/resit-results/resit-exam/done/publish-details')
            ->assertOk()
            ->assertJsonPath('data.publication_status', 'ready_to_publish')
            ->assertJsonPath('data.published_at', null);

        $this->getJson('/api/v1/resit-results/resit-exam/missing/publish-details')->assertNotFound();
    }
}
