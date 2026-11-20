<?php

namespace Tests\Feature\ExamEvaluation;

use App\Models\Schooladmin;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Bulk grade scale actions for exams and resit exams, over HTTP. */
class BulkGradeScaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'A', 'name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'a', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        DB::table('exam_types')->insert(['id' => 'ca-1', 'exam_name' => 'First CA', 'semester' => 'first', 'type' => 'ca', 'program_name' => 'ca1', 'semester_id' => 'first']);
        DB::table('exam_types')->insert(['id' => 'resit-1', 'exam_name' => 'First Resit', 'semester' => 'first', 'type' => 'resit', 'program_name' => 'r1', 'semester_id' => 'first']);
        foreach (['ca-a', 'ca-b'] as $id) {
            DB::table('exams')->insert(['id' => $id, 'exam_type_id' => 'ca-1', 'school_year_id' => 'sy', 'max_score' => 30, 'school_branch_id' => 'branch-1']);
        }
        DB::table('resit_exams')->insert(['id' => 'resit', 'exam_type_id' => 'resit-1', 'school_year_id' => 'sy', 'max_score' => 100, 'school_branch_id' => 'branch-1']);

        DB::table('letter_grades')->insert(['id' => 'lg-a', 'letter_grade' => 'A']);
        foreach ([['ca', 30], ['resit', 100]] as [$type, $max]) {
            DB::table('grade_scale_categories')->insert(['id' => "sys-$type", 'title' => "$type /$max", 'status' => 'active', 'exam_type' => $type]);
            DB::table('school_grade_scale_categories')->insert(['id' => "school-$type", 'max_score' => $max, 'status' => 'active', 'grades_category_id' => "sys-$type", 'school_branch_id' => 'branch-1']);
            DB::table('grade_scales')->insert(['id' => "gs-$type", 'grade_points' => 4, 'result' => 'passed', 'resit_result' => 'no_resit', 'minimum_score' => 0, 'maximum_score' => $max, 'performance' => 'A', 'school_branch_id' => 'branch-1', 'letter_grade_id' => 'lg-a', 'grades_category_id' => "school-$type"]);
        }
    }

    public function test_bulk_exam_grading_stores_the_school_grade_scale_on_every_exam(): void
    {
        $this->postJson('/api/v1/exam/bulk-add-grading-configs', ['exam_grading' => [
            ['exam_id' => 'ca-a', 'grades_config_Id' => 'school-ca'],
            ['exam_id' => 'ca-b', 'grades_config_Id' => 'school-ca'],
        ]])->assertOk();

        $this->assertSame(['school-ca', 'school-ca'], DB::table('exams')->orderBy('id')->pluck('grades_category_id')->all());
    }

    public function test_bulk_exam_grading_changes_nothing_when_one_exam_does_not_fit(): void
    {
        $this->postJson('/api/v1/exam/bulk-add-grading-configs', ['exam_grading' => [
            ['exam_id' => 'ca-a', 'grades_config_Id' => 'school-ca'],
            ['exam_id' => 'ca-b', 'grades_config_Id' => 'school-resit'],
        ]])->assertStatus(422);

        $this->assertSame([null, null], DB::table('exams')->orderBy('id')->pluck('grades_category_id')->all());
    }

    public function test_bulk_resit_grading_stores_the_school_grade_scale(): void
    {
        $this->postJson('/api/v1/resit-exam/grade-scale/bulk-add', ['exam_grading' => [
            ['resit_exam_id' => 'resit', 'grades_config_Id' => 'school-resit'],
        ]])->assertOk()->assertJsonPath('data.0.grading_added', true);

        $this->assertSame('school-resit', DB::table('resit_exams')->where('id', 'resit')->value('grades_category_id'));
    }

    public function test_the_migration_drops_grading_added_where_it_exists_and_is_safe_to_repeat(): void
    {
        foreach (['exams', 'resit_exams'] as $table) {
            Schema::table($table, fn(Blueprint $t) => $t->boolean('grading_added')->default(false));
        }

        $migration = require database_path('migrations/03_academics/2026_11_19_130000_drop_grading_added_from_exams.php');
        $migration->up();
        $migration->up();

        $this->assertFalse(Schema::hasColumn('exams', 'grading_added'));
        $this->assertFalse(Schema::hasColumn('resit_exams', 'grading_added'));
        $this->assertSame(2, DB::table('exams')->count(), 'Rows are kept.');
    }
}
