<?php

namespace Tests\Feature\GradeScale;

use App\Models\Job\SystemJob;
use App\Models\Schooladmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * "Defined Letter Grade Scheme" (grade.allowed_letter_grades) and "Maximum
 * Attainable GPA" (grade.max_gpa) are enforced wherever a grade scale is
 * written, and the settings cannot be changed under grades already in use.
 */
class GradeSchemeEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private const CAT_1 = '11111111-1111-4111-8111-111111111111';
    private const CAT_2 = '22222222-2222-4222-8222-222222222222';

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert([
            'id' => 'admin-1', 'first_name' => 'Fon', 'last_name' => 'Nathalie', 'name' => 'Fon Nathalie',
            'email' => 'fon@example.com', 'password' => 'x', 'username' => 'fnathalie', 'school_branch_id' => 'branch-1',
        ]);
        foreach (['A', 'B', 'C', 'F'] as $letter) {
            DB::table('letter_grades')->insert(['id' => "lg-$letter", 'letter_grade' => $letter]);
        }
        DB::table('grade_scale_categories')->insert([
            ['id' => 'sys-1', 'title' => 'Level One Exam', 'exam_type' => 'exam'],
            ['id' => 'sys-2', 'title' => 'Level Two Exam', 'exam_type' => 'exam'],
        ]);
        DB::table('school_grade_scale_categories')->insert([
            ['id' => self::CAT_1, 'school_branch_id' => 'branch-1', 'grades_category_id' => 'sys-1'],
            ['id' => self::CAT_2, 'school_branch_id' => 'branch-1', 'grades_category_id' => 'sys-2'],
        ]);

        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Grade Settings', 'key' => 'setting.category.grade']);
        $this->setting('grade.allowed_letter_grades', 'json', ['lg-A', 'lg-B', 'lg-F']); // C is not in the scheme
        $this->setting('grade.max_gpa', 'decimal', 4.0);
        $this->setting('grade.passing_gpa', 'decimal', 2.0);

        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');
    }

    private function setting(string $key, string $type, mixed $value): void
    {
        DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat', 'default_value' => json_encode($value)]);
        DB::table('school_branch_settings')->insert(['id' => "set-$key", 'school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$key", 'value' => json_encode($value)]);
    }

    private function grade(string $letter, float $points, float $min, float $max): array
    {
        return [
            'letter_grade_id' => "lg-$letter", 'grade_points' => $points, 'minimum_score' => $min, 'maximum_score' => $max,
            'performance' => 'x', 'result' => $points > 0 ? 'passed' : 'failed', 'resit_result' => $points > 0 ? 'no_resit' : 'resit',
        ];
    }

    private function create(array $grades, string $category = self::CAT_1)
    {
        return $this->postJson('/api/v1/grade-scale/create', ['grades_category_id' => $category, 'grade_max_score' => 100, 'grade_scales' => $grades]);
    }

    private function updateSetting(string $key, mixed $value)
    {
        return $this->patchJson('/api/v1/school-branch-setting/update', ['setting_id' => "set-$key", 'value' => $value]);
    }

    public function test_a_grade_scale_within_the_scheme_is_created(): void
    {
        $this->create([$this->grade('A', 4.0, 70, 100), $this->grade('B', 3.0, 50, 69.99), $this->grade('F', 0, 0, 49.99)])->assertStatus(201);
        $this->assertSame(3, DB::table('grade_scales')->count());
    }

    public function test_a_letter_outside_the_scheme_is_refused(): void
    {
        $this->create([$this->grade('A', 4.0, 70, 100), $this->grade('C', 2.0, 0, 69.99)])
            ->assertStatus(422)
            ->assertJsonPath('errors.title', 'Letter Grade Not Allowed');
        $this->assertSame(0, DB::table('grade_scales')->count());
    }

    public function test_grade_points_above_the_maximum_gpa_are_refused_on_create_and_update(): void
    {
        $this->create([$this->grade('A', 4.5, 70, 100)])->assertStatus(422)->assertJsonPath('errors.title', 'Grade Points Too High');

        $this->create([$this->grade('A', 4.0, 70, 100)])->assertStatus(201);
        $id = DB::table('grade_scales')->value('id');
        $this->patchJson('/api/v1/grade-scale/update', ['grades_category_id' => self::CAT_1, 'grade_scales' => [['grade_scale_id' => $id, 'grade_points' => 4.01]]])
            ->assertStatus(422)->assertJsonPath('errors.title', 'Grade Points Too High');
        $this->assertSame(4.0, (float) DB::table('grade_scales')->value('grade_points'));
    }

    public function test_a_school_on_a_five_point_scale_can_use_five_points(): void
    {
        DB::table('school_branch_settings')->where('id', 'set-grade.max_gpa')->update(['value' => json_encode(5.0)]);

        $this->create([$this->grade('A', 5.0, 70, 100)])->assertStatus(201);
    }

    public function test_copying_a_scale_that_no_longer_fits_the_scheme_is_refused(): void
    {
        // A scale saved before the maximum was lowered (inserted directly).
        DB::table('grade_scales')->insert(['id' => 'old', 'school_branch_id' => 'branch-1', 'grades_category_id' => self::CAT_1, 'letter_grade_id' => 'lg-A', 'grade_points' => 4.0, 'minimum_score' => 0, 'maximum_score' => 100, 'performance' => 'x', 'result' => 'passed', 'resit_result' => 'no_resit']);
        DB::table('school_branch_settings')->where('id', 'set-grade.max_gpa')->update(['value' => json_encode(3.0)]);

        $this->postJson('/api/v1/grade-scale/copy/source-category/' . self::CAT_1 . '/target-category/' . self::CAT_2 . '')->assertStatus(422);
        $this->postJson('/api/v1/grade-scale/bulk-copy', ['source_category_id' => self::CAT_1, 'target_category_ids' => [self::CAT_2]])->assertStatus(422);
        $this->assertSame(0, DB::table('grade_scales')->where('grades_category_id', self::CAT_2)->count());
    }

    public function test_the_form_offers_only_scheme_letters_and_generates_points_on_the_schools_scale(): void
    {
        DB::table('school_branch_settings')->where('id', 'set-grade.max_gpa')->update(['value' => json_encode(5.0)]);

        $grades = collect($this->getJson('/api/v1/grade-scale/category/' . self::CAT_1 . '/configType/automatic/maxScore/100')->assertOk()->json('data.grade_scales'));

        $this->assertEqualsCanonicalizing(['A', 'B', 'F'], $grades->pluck('letter_grade')->all());
        $this->assertSame('4.87', $grades->firstWhere('letter_grade', 'A')['configuration']['grade_points'], '3.90 on a 4.0 scale is 4.87 on a 5.0 scale.');
        $this->assertTrue($grades->every(fn($grade) => (float) $grade['configuration']['grade_points'] <= 5.0));
    }

    public function test_the_settings_cannot_be_changed_under_grades_in_use(): void
    {
        $this->create([$this->grade('A', 4.0, 70, 100), $this->grade('B', 3.0, 0, 69.99)])->assertStatus(201);

        $this->updateSetting('grade.max_gpa', 3.5)->assertStatus(422)->assertJsonPath('errors.title', 'Grade Points Above This Maximum');
        $this->updateSetting('grade.max_gpa', 1.5)->assertStatus(422);
        $this->updateSetting('grade.max_gpa', 5.0)->assertOk();
        $this->updateSetting('grade.passing_gpa', 5.5)->assertStatus(422)->assertJsonPath('errors.title', 'Above the Maximum GPA');

        $this->updateSetting('grade.allowed_letter_grades', ['lg-A', 'lg-F'])->assertStatus(422)->assertJsonPath('errors.title', 'Letter Grades in Use');
        $this->updateSetting('grade.allowed_letter_grades', ['lg-A', 'lg-B', 'lg-C'])->assertOk();
    }

    public function test_the_import_skips_rows_outside_the_scheme(): void
    {
        Storage::fake('r2');
        $csv = "grade_title,grade_max_score,grade,min_score,max_score,grade_points,result,resit_result,performance\n"
            . "Level One Exam,100,A,70,100,4,passed,no resit,Excellent\n"
            . "Level One Exam,100,C,50,69.99,2,passed,no resit,Fair\n"
            . "Level One Exam,100,B,40,49.99,4.2,passed,no resit,Good\n";
        $fields = ['grade_title', 'grade_max_score', 'grade', 'min_score', 'max_score', 'grade_points', 'result', 'resit_result', 'performance'];

        $response = $this->post('/api/v1/grade-scale/import', [
            'file' => UploadedFile::fake()->createWithContent('g.csv', $csv),
            'mapping' => ['standardFields' => array_combine($fields, $fields)],
        ], ['Accept' => 'application/json'])->assertStatus(202);

        $job = SystemJob::findOrFail($response->json('data.id'));
        $this->assertSame(1, $job->successful_items);
        $this->assertEqualsCanonicalizing([
            'Row 3: This letter grade is not part of your Defined Letter Grade Scheme (Settings › Grade Settings).',
            'Row 4: Grade points cannot exceed your Maximum Attainable GPA of 4.00.',
        ], collect($job->result['error_groups'])->pluck('summary')->all());
    }
}
