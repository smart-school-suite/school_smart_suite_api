<?php

namespace Tests\Feature\ExamEvaluation;

use App\Models\Exam\ExamCandidate;
use App\Models\StudentResults;
use App\Services\Exam\ExamScoreService;
use App\Services\ExamEvaluation\CandidateResultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** The minimum passing GPA (grade.passing_gpa) decides a candidate's CA or exam result. */
class CandidateResultTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Grades', 'key' => 'setting.category.grade']);
        DB::table('setting_definations')->insert(['id' => 'def-pass', 'name' => 'grade.passing_gpa', 'key' => 'grade.passing_gpa', 'data_type' => 'decimal', 'setting_category_id' => 'cat', 'default_value' => json_encode(2.0)]);

        DB::table('levels')->insert(['id' => 'lvl-1', 'name' => 'Level 100', 'level' => 100, 'program_name' => 'l1', 'level_type_id' => 't']);
        DB::table('specialties')->insert(['id' => 'swe', 'specialty_name' => 'SWE', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        DB::table('school_academic_years')->insert(['id' => 'sy', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'swe', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);
        DB::table('exams')->insert(['id' => 'exam', 'exam_type_id' => 't', 'school_year_id' => 'sy', 'start_date' => '2027-01-10', 'end_date' => '2027-01-20', 'max_score' => 100, 'school_branch_id' => 'branch-1']);
        DB::table('students')->insert(['id' => 'stu', 'name' => 'Ada', 'first_name' => 'Ada', 'last_name' => 'L', 'email' => 'ada@example.com', 'password' => 'x', 'username' => 'ada', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'guardian_id' => 'g', 'student_batch_id' => 'batch', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        DB::table('exam_candidates')->insert(['id' => 'cand', 'student_id' => 'stu', 'exam_id' => 'exam', 'school_branch_id' => 'branch-1']);

        foreach ([['A', 4, 'passed', 80, 100], ['C', 2, 'passed', 50, 59.99], ['F', 0, 'failed', 0, 39.99]] as [$letter, $points, $result, $min, $max]) {
            DB::table('letter_grades')->insert(['id' => "lg-$letter", 'letter_grade' => $letter]);
            DB::table('grade_scales')->insert(['id' => "gs-$letter", 'grade_points' => $points, 'result' => $result, 'resit_result' => $result === 'failed' ? 'resit' : 'no_resit', 'minimum_score' => $min, 'maximum_score' => $max, 'performance' => $letter, 'letter_grade_id' => "lg-$letter", 'school_branch_id' => 'branch-1', 'grades_category_id' => 'cat-1']);
        }
    }

    private function course(string $id, ?int $credit): void
    {
        DB::table('courses')->insert(['id' => $id, 'course_code' => strtoupper($id), 'course_title' => $id, 'credit' => $credit ?? 0, 'school_branch_id' => 'branch-1', 'semester_id' => 'first']);
    }

    private function score(string $course, string $grade, float $score): void
    {
        DB::table('exam_scores')->insert(['id' => "score-$course", 'score' => $score, 'candidate_id' => 'cand', 'course_id' => $course, 'exam_id' => 'exam', 'grade_id' => "gs-$grade", 'school_branch_id' => 'branch-1']);
    }

    private function passingGpa(float $gpa): void
    {
        DB::table('school_branch_settings')->insert(['id' => 'set-pass', 'school_branch_id' => 'branch-1', 'setting_defination_id' => 'def-pass', 'value' => json_encode($gpa)]);
    }

    private function record(): array
    {
        return app(CandidateResultService::class)->record(ExamCandidate::find('cand'));
    }

    public function test_the_gpa_is_weighted_by_credit_and_compared_with_the_passing_gpa(): void
    {
        $this->course('algo', 4);
        $this->course('ethics', 2);
        $this->score('algo', 'A', 85);
        $this->score('ethics', 'C', 55);

        $result = $this->record();

        $this->assertSame(3.33, $result['gpa'], '(4×4 + 2×2) / 6 credits.');
        $this->assertSame(['passed', 2.0, 0.0], [$result['result'], $result['passing_gpa'], $result['gpa_shortfall']]);
        $stored = StudentResults::sole();
        $this->assertSame(['passed', 3.33, 140.0, 'lvl-1', 'swe', 'batch'], [$stored->exam_status, (float) $stored->gpa, (float) $stored->total_score, $stored->level_id, $stored->specialty_id, $stored->student_batch_id]);
    }

    public function test_a_gpa_below_the_passing_gpa_fails_even_when_every_course_is_passed(): void
    {
        $this->passingGpa(3.5);
        $this->course('algo', 4);
        $this->course('ethics', 2);
        $this->score('algo', 'A', 85);
        $this->score('ethics', 'C', 55);

        $result = $this->record();

        $this->assertSame(['failed', 3.5, 0.17, 0], [$result['result'], $result['passing_gpa'], $result['gpa_shortfall'], $result['courses_failed']]);
        $this->assertSame('failed', StudentResults::sole()->exam_status);
    }

    public function test_reaching_the_passing_gpa_exactly_passes_and_a_course_without_credit_counts_once(): void
    {
        $this->passingGpa(2.0);
        $this->course('algo', 0);
        $this->course('ethics', null);
        $this->score('algo', 'A', 85);
        $this->score('ethics', 'F', 20);

        $result = $this->record();

        $this->assertSame([2.0, 'passed', 1], [$result['gpa'], $result['result'], $result['courses_failed']]);
    }

    public function test_recording_again_updates_the_result_and_deleting_the_scores_removes_it(): void
    {
        $this->course('algo', 4);
        $this->score('algo', 'F', 20);
        $this->assertSame('failed', $this->record()['result']);

        DB::table('exam_scores')->where('id', 'score-algo')->update(['grade_id' => 'gs-A', 'score' => 90]);
        $this->assertSame('passed', $this->record()['result']);
        $this->assertSame(1, StudentResults::count());

        app(ExamScoreService::class)->deleteCaExamScoresCandidateId('cand', (object) ['id' => 'branch-1']);
        $this->assertSame(0, StudentResults::count());
    }

    public function test_the_score_summary_shows_the_result_against_the_passing_gpa(): void
    {
        $this->passingGpa(3.0);
        $this->course('algo', 4);
        $this->score('algo', 'C', 55);

        $summary = app(ExamScoreService::class)->getCaExamScoresCandidateId('cand', (object) ['id' => 'branch-1'])['summary'];

        $this->assertSame([2.0, 3.0, 'failed', 1.0], [$summary['gpa'], $summary['passing_gpa'], $summary['result'], $summary['gpa_shortfall']]);
    }

    public function test_updating_exam_scores_keeps_the_ca_in_the_stored_score_and_records_the_result(): void
    {
        \Illuminate\Support\Facades\Bus::fake();
        DB::table('exam_types')->insert(['id' => 'ca-1', 'exam_name' => 'First CA', 'semester' => 'first', 'type' => 'ca', 'program_name' => 'ca1', 'semester_id' => 'first']);
        DB::table('exam_types')->insert(['id' => 'exam-1', 'exam_name' => 'First Exam', 'semester' => 'first', 'type' => 'exam', 'program_name' => 'e1', 'semester_id' => 'first']);
        DB::table('exams')->where('id', 'exam')->update(['exam_type_id' => 'exam-1', 'grades_category_id' => 'cat-1']);
        DB::table('exams')->insert(['id' => 'ca', 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy', 'start_date' => '2026-12-01', 'end_date' => '2026-12-05', 'max_score' => 30, 'school_branch_id' => 'branch-1']);
        DB::table('exam_candidates')->insert(['id' => 'cand-ca', 'student_id' => 'stu', 'exam_id' => 'ca', 'school_branch_id' => 'branch-1']);
        $this->course('algo', 4);
        DB::table('exam_scores')->insert(['id' => 'ca-algo', 'score' => 25, 'candidate_id' => 'cand-ca', 'course_id' => 'algo', 'exam_id' => 'ca', 'grade_id' => 'gs-A', 'school_branch_id' => 'branch-1']);
        $this->score('algo', 'C', 55); // 25 CA + 30 exam

        $response = app(\App\Services\ExamEvaluation\UpdateExamScoreService::class)->updateExamScore(
            ['candidate_id' => 'cand', 'scores' => [['score_id' => 'score-algo', 'score' => 60]]],
            (object) ['id' => 'branch-1'],
            (object) ['id' => 'admin'],
        );

        $this->assertSame(85.0, (float) DB::table('exam_scores')->where('id', 'score-algo')->value('score'), '25 CA + 60 exam, as when scores are added.');
        $this->assertSame('gs-A', DB::table('exam_scores')->where('id', 'score-algo')->value('grade_id'));
        $this->assertSame(['passed', 4.0], [$response['result']['result'], $response['result']['gpa']]);
        $this->assertSame('passed', StudentResults::where('exam_id', 'exam')->sole()->exam_status);
    }
}
