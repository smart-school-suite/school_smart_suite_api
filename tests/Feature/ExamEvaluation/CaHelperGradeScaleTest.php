<?php

namespace Tests\Feature\ExamEvaluation;

use App\Services\Exam\ExamService;
use App\Services\ExamEvaluation\ExamEvalutionHelperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CaHelperGradeScaleTest extends TestCase
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
        DB::table('exams')->insert(['id' => 'ca', 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy', 'start_date' => '2026-12-01', 'end_date' => '2026-12-05', 'max_score' => 30, 'school_branch_id' => 'branch-1']);
        DB::table('students')->insert(['id' => 'stu', 'name' => 'Ada', 'first_name' => 'Ada', 'last_name' => 'L', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'ada', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        DB::table('exam_candidates')->insert(['id' => 'cand', 'student_id' => 'stu', 'exam_id' => 'ca', 'school_branch_id' => 'branch-1']);
        DB::table('grade_scale_categories')->insert(['id' => 'sys-ca', 'title' => 'CA /30', 'status' => 'active', 'exam_type' => 'ca']);
        DB::table('school_grade_scale_categories')->insert(['id' => 'school-ca', 'max_score' => 30, 'status' => 'active', 'grades_category_id' => 'sys-ca', 'school_branch_id' => 'branch-1']);
        DB::table('letter_grades')->insert(['id' => 'lg-a', 'letter_grade' => 'A']);
        DB::table('grade_scales')->insert(['id' => 'gs-a', 'grade_points' => 4, 'result' => 'passed', 'resit_result' => 'no_resit', 'minimum_score' => 0, 'maximum_score' => 30, 'performance' => 'A', 'school_branch_id' => 'branch-1', 'letter_grade_id' => 'lg-a', 'grades_category_id' => 'school-ca']);
    }

    public function test_the_ca_helper_finds_a_grade_scale_attached_to_the_ca(): void
    {
        app(ExamService::class)->addExamGradeScale('ca', $this->school, 'school-ca', (object) ['id' => 'admin']);
        $this->assertSame('school-ca', DB::table('exams')->where('id', 'ca')->value('grades_category_id'));

        $data = app(ExamEvalutionHelperService::class)->getCaExamEvaluationHelperData($this->school, 'cand');

        $this->assertCount(1, $data['grade_scale']);
    }

    public function test_bulk_grading_stores_the_school_category_so_the_ca_helper_finds_it(): void
    {
        app(ExamService::class)->bulkAddExamGrading([['exam_id' => 'ca', 'grades_config_Id' => 'school-ca']], $this->school, (object) ['id' => 'admin']);

        $this->assertSame('school-ca', DB::table('exams')->where('id', 'ca')->value('grades_category_id'), 'Not the system category id (sys-ca).');
        $this->assertCount(1, app(ExamEvalutionHelperService::class)->getCaExamEvaluationHelperData($this->school, 'cand')['grade_scale']);
    }

    public function test_bulk_grading_applies_the_same_checks_as_a_single_attach(): void
    {
        DB::table('school_grade_scale_categories')->insert(['id' => 'school-empty', 'max_score' => 30, 'status' => 'active', 'grades_category_id' => 'sys-ca', 'school_branch_id' => 'branch-1']);

        try {
            app(ExamService::class)->bulkAddExamGrading([['exam_id' => 'ca', 'grades_config_Id' => 'school-empty']], $this->school, (object) ['id' => 'admin']);
            $this->fail('An unconfigured grade scale was attached.');
        } catch (\App\Exceptions\AppException $e) {
            $this->assertSame('Incomplete Configuration', $e->getTitle());
        }
        $this->assertNull(DB::table('exams')->where('id', 'ca')->value('grades_category_id'));
    }

    public function test_the_repair_migration_points_exams_with_a_system_category_id_at_the_school_category(): void
    {
        DB::table('exams')->where('id', 'ca')->update(['grades_category_id' => 'sys-ca']);
        DB::table('exams')->insert(['id' => 'orphan', 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy', 'max_score' => 30, 'school_branch_id' => 'branch-1', 'grades_category_id' => 'nowhere']);

        $migration = require database_path('migrations/03_academics/2026_11_19_120000_repair_exam_grade_scale_references.php');
        $migration->up();

        $this->assertSame('school-ca', DB::table('exams')->where('id', 'ca')->value('grades_category_id'));
        $this->assertNull(DB::table('exams')->where('id', 'orphan')->value('grades_category_id'), 'No matching school category: cleared.');
        $this->assertCount(1, app(ExamEvalutionHelperService::class)->getCaExamEvaluationHelperData($this->school, 'cand')['grade_scale']);
    }
}
