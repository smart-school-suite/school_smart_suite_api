<?php

namespace Tests\Feature\AcademicYear;

use App\Models\SchoolBranchSetting;
use App\Models\Schooladmin;
use App\Services\AcademicYear\SchoolAcademicYearCreationOptions;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SchoolAcademicYearCreationOptionsTest extends TestCase
{
    use RefreshDatabase;

    private const PREFIX = 'academic_year.auto_create_';

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        DB::table('system_academic_years')->insert([
            ['id' => 'year-25', 'name' => '2025/2026', 'year_start' => 2025, 'year_end' => 2026],
            ['id' => 'year-26', 'name' => '2026/2027', 'year_start' => 2026, 'year_end' => 2027],
            ['id' => 'year-27', 'name' => '2027/2028', 'year_start' => 2027, 'year_end' => 2028],
            ['id' => 'year-28', 'name' => '2028/2029', 'year_start' => 2028, 'year_end' => 2029],
        ]);
        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100-Level Start', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        DB::table('departments')->insert(['id' => 'computing', 'department_name' => 'Computing', 'school_branch_id' => 'branch-1']);
        foreach (['swe-2' => 'lvl-2', 'swe-1' => 'lvl-1'] as $id => $level) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'computing', 'level_id' => $level]);
        }

        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Academic Year Settings', 'key' => 'setting.category.academic_year']);
        foreach ([
            'mode' => ['string', 'disabled'],
            'levels' => ['json', ['lvl-1', 'lvl-2']],
            'across_specialties' => ['boolean', false],
            'across_all_levels' => ['boolean', false],
            'require_students' => ['boolean', false],
        ] as $name => [$type, $value]) {
            DB::table('setting_definations')->insert(['id' => "def-$name", 'name' => $name, 'key' => self::PREFIX . $name, 'data_type' => $type, 'setting_category_id' => 'cat']);
            SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$name", 'value' => $value]);
        }
    }

    private function schoolYear(string $id, string $specialty, string $year, string $start, string $end): void
    {
        DB::table('school_academic_years')->insert(['id' => $id, 'start_date' => $start, 'end_date' => $end, 'specialty_id' => $specialty, 'system_academic_year_id' => $year, 'school_branch_id' => 'branch-1']);
    }

    private function creationOptions(string $today = '2026-10-15'): array
    {
        return app(SchoolAcademicYearCreationOptions::class)->describe((object) ['id' => 'branch-1'], Carbon::parse($today));
    }

    private function entry(array $options, string $specialty, string $year): array
    {
        return collect(collect($options['specialties'])->firstWhere('id', $specialty)['years'])->firstWhere('system_academic_year_id', $year);
    }

    public function test_it_offers_the_current_and_next_year_with_a_typical_suggestion(): void
    {
        $options = $this->creationOptions();

        $this->assertSame(['year-26', 'year-27'], array_column($options['academic_years'], 'id'));
        $this->assertSame([true, false], array_column($options['academic_years'], 'current'));
        $this->assertSame(['typical', 'typical'], array_column($options['academic_years'], 'suggestion_source'));
        $this->assertSame(['swe-1', 'swe-2'], array_column($options['specialties'], 'id'));
        $this->assertSame('Software Engineering · Level 100', $options['specialties'][0]['label']);
        $this->assertSame('Computing', $options['specialties'][0]['department_name']);

        $entry = $this->entry($options, 'swe-1', 'year-26');
        $this->assertFalse($entry['created']);
        $this->assertSame(['start_date' => '2026-09-01', 'end_date' => '2027-06-30'], $entry['suggested']);
        $this->assertSame(['start_date' => '2026-01-01', 'end_date' => '2027-12-31', 'after' => null, 'before' => null], $entry['allowed']);
    }

    public function test_a_created_year_is_marked_and_the_next_one_starts_after_it(): void
    {
        $this->schoolYear('sy-1', 'swe-1', 'year-26', '2026-09-15', '2027-07-15');

        $options = $this->creationOptions();
        $created = $this->entry($options, 'swe-1', 'year-26');
        $next = $this->entry($options, 'swe-1', 'year-27');

        $this->assertTrue($created['created']);
        $this->assertSame(['id' => 'sy-1', 'start_date' => '2026-09-15', 'end_date' => '2027-07-15'], $created['existing']);
        $this->assertSame('2027-07-16', $next['allowed']['start_date']);
        $this->assertSame(['name' => '2026/2027', 'end_date' => '2027-07-15'], $next['allowed']['after']);
        $this->assertSame(['start_date' => '2027-09-15', 'end_date' => '2028-07-15'], $next['suggested']);
        $this->assertSame('last_year', $options['academic_years'][1]['suggestion_source']);
    }

    public function test_dates_used_by_other_classes_are_suggested_first(): void
    {
        $this->schoolYear('sy-2', 'swe-2', 'year-26', '2026-10-01', '2027-07-31');

        $options = $this->creationOptions();

        $this->assertSame('other_classes', $options['academic_years'][0]['suggestion_source']);
        $this->assertSame(['start_date' => '2026-10-01', 'end_date' => '2027-07-31'], $this->entry($options, 'swe-1', 'year-26')['suggested']);
    }

    public function test_a_later_year_bounds_the_safe_range(): void
    {
        $this->schoolYear('sy-3', 'swe-1', 'year-27', '2027-08-15', '2028-06-30');

        $entry = $this->entry($this->creationOptions(), 'swe-1', 'year-26');

        $this->assertSame('2027-08-14', $entry['allowed']['end_date']);
        $this->assertSame(['name' => '2027/2028', 'start_date' => '2027-08-15'], $entry['allowed']['before']);
    }

    public function test_copy_targets_list_the_levels_an_automatic_copy_would_reach(): void
    {
        $this->assertSame([], $this->creationOptions()['specialties'][0]['copy_targets']);

        SchoolBranchSetting::where('setting_defination_id', 'def-mode')->first()->update(['value' => 'same_specialty']);
        $this->schoolYear('sy-2', 'swe-2', 'year-26', '2026-09-01', '2027-06-30');

        $options = $this->creationOptions();

        $this->assertTrue($options['auto_creation']['enabled']);
        $this->assertSame([[
            'specialty_id' => 'swe-2',
            'label' => 'Software Engineering · Level 200',
            'existing_year_ids' => ['year-26'],
        ]], $options['specialties'][0]['copy_targets']);
    }

    public function test_the_endpoint_returns_the_options_for_the_admins_branch(): void
    {
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'B', 'name' => 'A B', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'ab', 'school_branch_id' => 'branch-1']);
        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');

        $this->getJson('/api/v1/school-academic-year/creation-options')
            ->assertOk()
            ->assertJsonCount(2, 'data.specialties')
            ->assertJsonPath('data.specialties.0.years.0.created', false);
    }
}
