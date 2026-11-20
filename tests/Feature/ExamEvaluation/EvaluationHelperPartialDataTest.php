<?php

namespace Tests\Feature\ExamEvaluation;

use App\Services\ExamEvaluation\ExamEvalutionHelperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The score dialogs' helper endpoints answer with partial data instead of a
 * 500 when a course was deleted, a CA score is missing or a grade is gone.
 */
class EvaluationHelperPartialDataTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->school = (object) ['id' => 'branch-1'];
        DB::table('levels')->insert(['id' => 'lvl-1', 'name' => 'Level 100', 'level' => 100, 'program_name' => 'l1', 'level_type_id' => 't']);
        DB::table('specialties')->insert(['id' => 'swe', 'specialty_name' => 'SWE', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        DB::table('school_academic_years')->insert(['id' => 'sy', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'swe', 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);
        DB::table('semesters')->insert(['id' => 'first', 'name' => 'First Semester', 'count' => 1, 'program_name' => 'HND']);
        DB::table('school_semesters')->insert(['id' => 'sem', 'semester_id' => 'first', 'school_year_id' => 'sy', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31', 'school_branch_id' => 'branch-1']);
        DB::table('exam_types')->insert(['id' => 'ca-1', 'exam_name' => 'First CA', 'semester' => 'first', 'type' => 'ca', 'program_name' => 'ca1', 'semester_id' => 'first']);
        DB::table('exam_types')->insert(['id' => 'exam-1', 'exam_name' => 'First Exam', 'semester' => 'first', 'type' => 'exam', 'program_name' => 'e1', 'semester_id' => 'first']);
        DB::table('grade_scale_categories')->insert(['id' => 'sys', 'title' => '/100', 'status' => 'active', 'exam_type' => 'exam']);
        DB::table('school_grade_scale_categories')->insert(['id' => 'scale', 'max_score' => 100, 'status' => 'active', 'grades_category_id' => 'sys', 'school_branch_id' => 'branch-1']);
        DB::table('letter_grades')->insert(['id' => 'lg-a', 'letter_grade' => 'A']);
        DB::table('grade_scales')->insert(['id' => 'gs-a', 'grade_points' => 4, 'result' => 'passed', 'resit_result' => 'no_resit', 'minimum_score' => 0, 'maximum_score' => 100, 'performance' => 'A', 'school_branch_id' => 'branch-1', 'letter_grade_id' => 'lg-a', 'grades_category_id' => 'scale']);
        DB::table('exams')->insert(['id' => 'ca', 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy', 'max_score' => 30, 'school_branch_id' => 'branch-1', 'grades_category_id' => 'scale']);
        DB::table('exams')->insert(['id' => 'exam', 'exam_type_id' => 'exam-1', 'school_year_id' => 'sy', 'max_score' => 100, 'school_branch_id' => 'branch-1', 'grades_category_id' => 'scale']);
        DB::table('students')->insert(['id' => 'stu', 'name' => 'Ada', 'first_name' => 'Ada', 'last_name' => 'L', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'ada', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        DB::table('exam_candidates')->insert(['id' => 'cand-ca', 'student_id' => 'stu', 'exam_id' => 'ca', 'school_branch_id' => 'branch-1']);
        DB::table('exam_candidates')->insert(['id' => 'cand-exam', 'student_id' => 'stu', 'exam_id' => 'exam', 'school_branch_id' => 'branch-1']);
        foreach (['algo' => 'Algorithms', 'db' => 'Databases'] as $id => $title) {
            DB::table('courses')->insert(['id' => $id, 'course_code' => strtoupper($id), 'course_title' => $title, 'credit' => 3, 'school_branch_id' => 'branch-1', 'semester_id' => 'first']);
        }

        // CA: algo scored, db scored with a deleted grade, a course that was deleted
        $this->score('ca-algo', 'cand-ca', 'ca', 'algo', 25, 'gs-a');
        $this->score('ca-db', 'cand-ca', 'ca', 'db', 20, 'gs-deleted');
        $this->score('ca-gone', 'cand-ca', 'ca', 'deleted-course', 10, 'gs-a');
        // Exam (stored as CA + exam): db has no CA score here, algo does
        $this->score('ex-algo', 'cand-exam', 'exam', 'algo', 85, 'gs-a');
        $this->score('ex-gone', 'cand-exam', 'exam', 'deleted-course', 40, 'gs-deleted');
    }

    private function score(string $id, string $candidate, string $exam, string $course, float $score, string $grade): void
    {
        DB::table('exam_scores')->insert(['id' => $id, 'score' => $score, 'candidate_id' => $candidate, 'course_id' => $course, 'exam_id' => $exam, 'grade_id' => $grade, 'school_branch_id' => 'branch-1']);
    }

    private function helper(): ExamEvalutionHelperService
    {
        return app(ExamEvalutionHelperService::class);
    }

    public function test_the_exam_update_helper_handles_a_deleted_course_and_a_missing_grade(): void
    {
        $scores = collect($this->helper()->getExamUpdateHelperData($this->school, 'cand-exam')['scores'])->keyBy('id');

        $this->assertSame([60.0, 25.0, 'Algorithms', 'A'], [$scores['ex-algo']['score'], $scores['ex-algo']['ca_score'], $scores['ex-algo']['course_title'], $scores['ex-algo']['grade']]);
        // the deleted course: its CA score (10) is still taken off; no title, no grade
        $this->assertSame([30.0, 10.0, null, null, 0.0], [$scores['ex-gone']['score'], $scores['ex-gone']['ca_score'], $scores['ex-gone']['course_title'], $scores['ex-gone']['grade'], $scores['ex-gone']['grade_points']]);
    }

    public function test_a_course_without_a_ca_score_counts_its_ca_as_zero(): void
    {
        DB::table('exam_scores')->insert(['id' => 'ex-db', 'score' => 50, 'candidate_id' => 'cand-exam', 'course_id' => 'db', 'exam_id' => 'exam', 'grade_id' => 'gs-a', 'school_branch_id' => 'branch-1']);
        DB::table('exam_scores')->where('id', 'ca-db')->delete();

        $scores = collect($this->helper()->getExamUpdateHelperData($this->school, 'cand-exam')['scores'])->keyBy('id');

        $this->assertSame([50.0, 0.0], [$scores['ex-db']['score'], $scores['ex-db']['ca_score']]);
    }

    public function test_the_ca_update_helper_handles_a_missing_grade(): void
    {
        $courses = collect($this->helper()->getCaExamUpdateHelperData($this->school, 'cand-ca')['courses'])->keyBy('id');

        $this->assertSame([20.0, 0.0, null, null], [$courses['ca-db']['score'], $courses['ca-db']['grade_points'], $courses['ca-db']['result'], $courses['ca-db']['grade']]);
        $this->assertSame([null, 'deleted-course'], [$courses['ca-gone']['course_title'], $courses['ca-gone']['course_id']]);
    }

    public function test_the_exam_helper_leaves_out_ca_scores_of_deleted_courses(): void
    {
        $caScores = $this->helper()->getExamEvaluationHelperData($this->school, 'cand-exam')['ca_scores'];

        $this->assertEqualsCanonicalizing(['algo', 'db'], collect($caScores)->pluck('course_id')->all());
    }
}
