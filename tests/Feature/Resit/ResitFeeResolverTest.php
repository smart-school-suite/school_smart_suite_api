<?php

namespace Tests\Feature\Resit;

use App\Services\Resit\ResitFeeResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** The resit fee follows the resit settings: standard, or by the student's level. */
class ResitFeeResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Resit Settings', 'key' => 'setting.category.resit']);
        $this->definition('resitFee.generalBilling', 'boolean', true);
        $this->definition('resitFee.levelBilling', 'boolean', false);
        $this->definition('resitFee.generalBillingFee', 'decimal', 3000);
        $this->definition('resitFee.levelBillingFee', 'json', []);

        foreach (['lvl-1' => 100, 'lvl-2' => 200, 'lvl-3' => 300] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 't']);
            DB::table('specialties')->insert(['id' => "swe-$level", 'specialty_name' => 'SWE', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => $id]);
            DB::table('school_academic_years')->insert(['id' => "sy-$level", 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => "swe-$level", 'system_academic_year_id' => 'sys', 'school_branch_id' => 'branch-1']);
            DB::table('exams')->insert(['id' => "exam-$level", 'exam_type_id' => 't', 'school_year_id' => "sy-$level", 'start_date' => '2027-01-10', 'end_date' => '2027-01-20', 'max_score' => 100, 'school_branch_id' => 'branch-1']);
        }
    }

    private function definition(string $key, string $type, mixed $default): void
    {
        DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat', 'default_value' => json_encode($default)]);
    }

    private function set(string $key, mixed $value): void
    {
        DB::table('school_branch_settings')->updateOrInsert(
            ['school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$key"],
            ['id' => "set-$key", 'value' => json_encode($value)]
        );
    }

    private function fee(string $exam): float
    {
        return (new ResitFeeResolver())->feeForExam('branch-1', $exam);
    }

    private function levelBilling(array $fees): void
    {
        $this->set('resitFee.generalBilling', false);
        $this->set('resitFee.levelBilling', true);
        $this->set('resitFee.levelBillingFee', $fees);
    }

    public function test_the_standard_fee_applies_to_every_level(): void
    {
        $this->set('resitFee.generalBillingFee', 4500);
        $this->set('resitFee.levelBillingFee', [['level_id' => 'lvl-1', 'fee' => 9999]]);

        $this->assertSame([4500.0, 4500.0, 4500.0], [$this->fee('exam-100'), $this->fee('exam-200'), $this->fee('exam-300')]);
    }

    public function test_level_billing_charges_the_fee_of_the_students_level(): void
    {
        $this->set('resitFee.generalBillingFee', 4500);
        $this->levelBilling([['level_id' => 'lvl-1', 'fee' => 2000], ['level_id' => 'lvl-2', 'fee' => 3500], ['level_id' => 'lvl-3', 'fee' => 5000]]);

        $this->assertSame([2000.0, 3500.0, 5000.0], [$this->fee('exam-100'), $this->fee('exam-200'), $this->fee('exam-300')]);
    }

    public function test_a_level_without_a_fee_falls_back_to_the_standard_fee(): void
    {
        $this->set('resitFee.generalBillingFee', 4500);
        $this->levelBilling([['level_id' => 'lvl-1', 'fee' => 2000]]);

        $this->assertSame(4500.0, $this->fee('exam-300'));
    }

    public function test_a_branch_without_settings_uses_the_defaults(): void
    {
        $this->assertSame(3000.0, $this->fee('exam-200'));
    }

    /** Calls a private method of a service, the way scoring does when a course is failed. */
    private function callPrivate(string $class, string $method, array $args): mixed
    {
        $service = app($class);
        $reflection = new \ReflectionMethod($service, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($service, $args);
    }

    public function test_resits_created_when_scores_are_added_or_updated_store_the_fee(): void
    {
        $this->levelBilling([['level_id' => 'lvl-1', 'fee' => 2000], ['level_id' => 'lvl-2', 'fee' => 3500], ['level_id' => 'lvl-3', 'fee' => 5000]]);

        $this->callPrivate(\App\Services\ExamEvaluation\AddExamScoreService::class, 'createResit', ['course-a', 'stu-1', 'exam-200', 'branch-1']);
        $this->callPrivate(\App\Services\ExamEvaluation\UpdateExamScoreService::class, 'syncResit', ['course-b', 'stu-1', 'exam-300', 'branch-1', true]);

        $fees = DB::table('student_resits')->orderBy('course_id')->pluck('fee', 'course_id')->map(fn($fee) => (float) $fee)->all();
        $this->assertSame(['course-a' => 3500.0, 'course-b' => 5000.0], $fees);
    }
}
