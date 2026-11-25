<?php

namespace Tests\Feature\Semester;

use App\Models\SchoolBranchSetting;
use App\Models\Schooladmin;
use App\Services\Semester\SchoolSemesterCreationOptions;
use App\Services\Semester\SemesterAutoCreationSettings as Settings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SetsSemesterLimit;
use Tests\TestCase;

class SchoolSemesterCreationOptionsTest extends TestCase
{
    use RefreshDatabase;
    use SetsSemesterLimit;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->school = (object) ['id' => 'branch-1'];

        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        DB::table('system_academic_years')->insert([
            ['id' => 'year-26', 'name' => '2026/2027', 'year_start' => 2026, 'year_end' => 2027],
            ['id' => 'year-25', 'name' => '2025/2026', 'year_start' => 2025, 'year_end' => 2026],
        ]);
        DB::table('semesters')->insert([
            ['id' => 'sem-2', 'name' => 'Second Semester', 'count' => 2, 'program_name' => 'SES'],
            ['id' => 'sem-1', 'name' => 'First Semester', 'count' => 1, 'program_name' => 'FIS'],
            ['id' => 'sem-3', 'name' => 'Third Semester', 'count' => 3, 'program_name' => 'THS'],
        ]);
        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100-Level Start', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        foreach (['swe-1' => 'lvl-1', 'swe-2' => 'lvl-2'] as $id => $level) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'computing', 'level_id' => $level]);
        }
        DB::table('school_academic_years')->insert([
            ['id' => 'sy-1', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'specialty_id' => 'swe-1', 'system_academic_year_id' => 'year-26', 'school_branch_id' => 'branch-1'],
            ['id' => 'sy-2', 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'specialty_id' => 'swe-2', 'system_academic_year_id' => 'year-26', 'school_branch_id' => 'branch-1'],
            ['id' => 'sy-old', 'start_date' => '2025-09-01', 'end_date' => '2026-06-30', 'specialty_id' => 'swe-1', 'system_academic_year_id' => 'year-25', 'school_branch_id' => 'branch-1'],
        ]);

        DB::table('setting_categories')->insert(['id' => 'cat-sem', 'name' => 'Semester Settings', 'key' => 'setting.category.semester']);
        $defaults = [
            Settings::ENABLED => ['boolean', false],
            Settings::ACROSS_LEVELS => ['boolean', false],
            Settings::ACROSS_SPECIALTY => ['boolean', false],
            Settings::ACROSS_DEPARTMENT_LEVELS => ['boolean', false],
            Settings::ACROSS_DEPARTMENT_SPECIALTY => ['boolean', true],
            Settings::LEVELS => ['json', ['lvl-1', 'lvl-2']],
            Settings::REQUIRE_STUDENTS => ['boolean', false],
            'level.start' => ['string', 'level_start_100'],
        ];
        foreach ($defaults as $key => [$type, $value]) {
            DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat-sem']);
            SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$key", 'value' => $value]);
        }
    }

    private function creationOptions(string $today = '2026-10-15'): array
    {
        return app(SchoolSemesterCreationOptions::class)->describe($this->school, Carbon::parse($today));
    }

    private function year(array $options, string $id): array
    {
        return collect($options['school_years'])->firstWhere('id', $id);
    }

    private function semester(array $year, string $id): array
    {
        return collect($year['semesters'])->firstWhere('id', $id);
    }

    public function test_an_empty_year_suggests_the_first_semester_in_the_first_half(): void
    {
        $year = $this->year($this->creationOptions(), 'sy-1');

        $this->assertSame(['sem-1', 'sem-2'], array_column($year['semesters'], 'id'));
        $this->assertSame('sem-1', $year['recommended_semester_id']);
        $this->assertSame(['start_date' => '2026-09-01', 'end_date' => '2027-01-29'], $this->semester($year, 'sem-1')['suggested']);
        $this->assertSame(['start_date' => '2027-01-30', 'end_date' => '2027-06-30'], $this->semester($year, 'sem-2')['suggested']);
        $this->assertSame(['start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'after' => null, 'before' => null], $this->semester($year, 'sem-2')['allowed']);
        $this->assertSame('Software Engineering · Level 100', $year['label']);
        $this->assertSame('2026/2027', $year['academic_year']);
        $this->assertSame(['2026-09-01', '2027-06-30'], [$year['start_date'], $year['end_date']]);
    }

    public function test_a_created_semester_is_marked_and_the_next_one_starts_the_day_after(): void
    {
        DB::table('school_semesters')->insert(['id' => 'ss-1', 'semester_id' => 'sem-1', 'school_year_id' => 'sy-1', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31', 'school_branch_id' => 'branch-1', 'timetable_published' => false]);

        $year = $this->year($this->creationOptions(), 'sy-1');
        $first = $this->semester($year, 'sem-1');

        $this->assertTrue($first['created']);
        $this->assertNull($first['suggested']);
        $this->assertSame(['id' => 'ss-1', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31'], $first['existing']);
        $this->assertSame('sem-2', $year['recommended_semester_id']);
        $this->assertSame(['start_date' => '2027-02-01', 'end_date' => '2027-06-30'], $this->semester($year, 'sem-2')['suggested']);
    }

    public function test_a_gap_between_two_created_semesters_is_suggested_for_the_missing_one(): void
    {
        DB::table('school_semesters')->insert([
            ['id' => 'ss-1', 'semester_id' => 'sem-1', 'school_year_id' => 'sy-1', 'start_date' => '2026-09-01', 'end_date' => '2026-12-15', 'school_branch_id' => 'branch-1', 'timetable_published' => false],
            ['id' => 'ss-3', 'semester_id' => 'sem-3', 'school_year_id' => 'sy-1', 'start_date' => '2027-04-01', 'end_date' => '2027-06-30', 'school_branch_id' => 'branch-1', 'timetable_published' => false],
        ]);

        $year = $this->year($this->creationOptions(), 'sy-1');

        $this->assertSame(['start_date' => '2026-12-16', 'end_date' => '2027-03-31'], $this->semester($year, 'sem-2')['suggested']);
        $this->assertSame([
            'start_date' => '2026-12-16',
            'end_date' => '2027-03-31',
            'after' => ['name' => 'First Semester', 'end_date' => '2026-12-15'],
            'before' => ['name' => 'Third Semester', 'start_date' => '2027-04-01'],
        ], $this->semester($year, 'sem-2')['allowed']);
    }

    public function test_years_are_ordered_ongoing_first_and_ended_last(): void
    {
        $options = $this->creationOptions('2026-10-15');

        $this->assertSame('2026-10-15', $options['today']);
        $this->assertFalse($options['auto_creation']['enabled']);
        $this->assertSame(['ongoing', 'ongoing', 'ended'], array_column($options['school_years'], 'status'));
        $this->assertSame('sy-old', end($options['school_years'])['id']);
        $this->assertSame('upcoming', $this->year($this->creationOptions('2026-08-01'), 'sy-1')['status']);
    }

    public function test_copy_targets_list_the_years_an_automatic_copy_would_reach(): void
    {
        $this->assertSame([], $this->year($this->creationOptions(), 'sy-1')['copy_targets']);

        SchoolBranchSetting::where('setting_defination_id', 'def-' . Settings::ENABLED)->first()->update(['value' => true]);
        DB::table('school_semesters')->insert(['id' => 'ss-2', 'semester_id' => 'sem-1', 'school_year_id' => 'sy-2', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31', 'school_branch_id' => 'branch-1', 'timetable_published' => false]);

        $options = $this->creationOptions();
        $targets = $this->year($options, 'sy-1')['copy_targets'];

        $this->assertTrue($options['auto_creation']['enabled']);
        $this->assertSame([[
            'school_year_id' => 'sy-2',
            'label' => 'Software Engineering · Level 200',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'existing_semester_ids' => ['sem-1'],
        ]], $targets);
    }

    public function test_the_endpoint_returns_the_options_for_the_admins_branch(): void
    {
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'B', 'name' => 'A B', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'ab', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        $this->getJson('/api/v1/school-semester/creation-options')
            ->assertOk()
            ->assertJsonCount(3, 'data.school_years')
            ->assertJsonPath('data.school_years.0.semesters.0.name', 'First Semester');
    }

    public function test_only_semesters_within_the_schools_limit_are_offered(): void
    {
        $this->assertSame(2, $this->creationOptions()['max_semesters']);

        $this->setSemesterLimit(3);
        $options = $this->creationOptions();
        $year = $this->year($options, 'sy-1');

        $this->assertSame(3, $options['max_semesters']);
        $this->assertSame(['sem-1', 'sem-2', 'sem-3'], array_column($year['semesters'], 'id'));
        $this->assertSame(['start_date' => '2026-09-01', 'end_date' => '2026-12-10'], $this->semester($year, 'sem-1')['suggested']);
    }

    public function test_the_limit_cannot_exceed_the_semesters_that_exist(): void
    {
        $this->setSemesterLimit(9);

        $this->assertSame(3, $this->creationOptions()['max_semesters']);
    }
}
