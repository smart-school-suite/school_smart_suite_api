<?php

namespace Tests\Feature\Course;

use App\Models\Schooladmin;
use App\Services\Course\CourseCreationOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SetsSemesterLimit;
use Tests\TestCase;

class CourseCreationOptionsTest extends TestCase
{
    use RefreshDatabase;
    use SetsSemesterLimit;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        DB::table('semesters')->insert([
            ['id' => 'sem-1', 'name' => 'First Semester', 'count' => 1, 'program_name' => 'FIS'],
            ['id' => 'sem-2', 'name' => 'Second Semester', 'count' => 2, 'program_name' => 'SES'],
            ['id' => 'sem-3', 'name' => 'Third Semester', 'count' => 3, 'program_name' => 'THS'],
        ]);
        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100-Level Start', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        DB::table('departments')->insert(['id' => 'computing', 'department_name' => 'Computing', 'school_branch_id' => 'branch-1']);
        foreach (['swe-2' => 'lvl-2', 'swe-1' => 'lvl-1'] as $id => $level) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'computing', 'level_id' => $level]);
        }

        $this->course('c-1', 'swe101', 'Programming I', 4, 'sem-1', 'swe-1');
        $this->course('c-2', 'SWE102', 'Mathematics', 3, 'sem-1', 'swe-1');
        $this->course('c-3', 'SWE201', 'Databases', 5, 'sem-2', 'swe-2');
    }

    private function course(string $id, string $code, string $title, int $credit, string $semester, string $specialty): void
    {
        DB::table('courses')->insert(['id' => $id, 'course_code' => $code, 'course_title' => $title, 'credit' => $credit, 'semester_id' => $semester, 'school_branch_id' => 'branch-1', 'status' => 'active']);
        DB::table('course_specialties')->insert(['id' => "cs-$id", 'course_id' => $id, 'specialty_id' => $specialty, 'school_branch_id' => 'branch-1']);
    }

    private function creationOptions(): array
    {
        return app(CourseCreationOptions::class)->describe((object) ['id' => 'branch-1']);
    }

    public function test_it_lists_specialties_with_their_courses_per_semester(): void
    {
        $options = $this->creationOptions();
        $first = $options['specialties'][0];

        $this->assertSame(['sem-1', 'sem-2'], array_column($options['semesters'], 'id'));
        $this->assertSame(['swe-1', 'swe-2'], array_column($options['specialties'], 'id'));
        $this->assertSame('Software Engineering · Level 100', $first['label']);
        $this->assertSame('Computing', $first['department_name']);
        $this->assertSame(2, $first['courses_count']);
        $this->assertSame(['semester_id' => 'sem-1', 'courses_count' => 2, 'credits' => 7], array_intersect_key($first['semesters'][0], array_flip(['semester_id', 'courses_count', 'credits'])));
        $this->assertSame(['SWE102', 'swe101'], array_column($first['semesters'][0]['courses'], 'course_code'));
        $this->assertSame(0, $first['semesters'][1]['courses_count']);
    }

    public function test_codes_are_listed_once_in_upper_case(): void
    {
        $this->assertEqualsCanonicalizing(['SWE101', 'SWE102', 'SWE201'], $this->creationOptions()['codes']);
    }

    public function test_semesters_follow_the_schools_limit(): void
    {
        $this->setSemesterLimit(3);

        $options = $this->creationOptions();

        $this->assertSame(['sem-1', 'sem-2', 'sem-3'], array_column($options['semesters'], 'id'));
        $this->assertCount(3, $options['specialties'][0]['semesters']);
    }

    public function test_the_endpoint_returns_the_options_for_the_admins_branch(): void
    {
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'B', 'name' => 'A B', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'ab', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        $this->getJson('/api/v1/course/creation-options')
            ->assertOk()
            ->assertJsonCount(2, 'data.specialties')
            ->assertJsonPath('data.specialties.0.semesters.0.courses_count', 2);
    }
}
