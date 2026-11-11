<?php

namespace Tests\Feature\AcademicYear;

use App\Exceptions\AppException;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\FeeSchedule;
use App\Models\Promotion\PromotionRun;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Handlers\AutoCreation\AutoCreateAcrossSwitchHandler;
use App\SchoolBranchSetting\Handlers\AutoCreation\AutoCreateModeHandler;
use App\SchoolBranchSetting\Handlers\AutoCreation\AutoCreateRequireStudentsHandler;
use App\Services\AcademicYear\SchoolAcademicYearAutoCreationService;
use App\Services\AcademicYear\SchoolAcademicYearService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class AutomaticAcademicYearCreationTest extends TestCase
{
    use RefreshDatabase;

    private const PREFIX = 'academic_year.auto_create_';

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->school = (object) ['id' => 'branch-1'];

        DB::table('system_academic_years')->insert(['id' => 'year-2099', 'name' => '2099/2100', 'year_start' => 2099, 'year_end' => 2100]);
        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100-Level Start', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200, 'lvl-3' => 300, 'lvl-4' => 400] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }

        // Computing: Software Engineering L1-L3, Networking L2 and L4. Business: Accounting L2. Branch 2: SWE L2.
        foreach ([
            ['swe-1', 'Software Engineering', 'lvl-1', 'computing', 'branch-1'],
            ['swe-2', 'Software Engineering', 'lvl-2', 'computing', 'branch-1'],
            ['swe-3', 'Software Engineering', 'lvl-3', 'computing', 'branch-1'],
            ['net-2', 'Networking', 'lvl-2', 'computing', 'branch-1'],
            ['net-4', 'Networking', 'lvl-4', 'computing', 'branch-1'],
            ['acc-2', 'Accounting', 'lvl-2', 'business', 'branch-1'],
            ['b2-swe-2', 'Software Engineering', 'lvl-2', 'computing', 'branch-2'],
        ] as [$id, $name, $level, $department, $branch]) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => $branch, 'department_id' => $department, 'level_id' => $level]);
        }

        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Academic Year Settings', 'key' => 'setting.category.academic_year']);
        foreach ([
            'mode' => ['string', 'disabled'],
            'levels' => ['json', ['lvl-1', 'lvl-2', 'lvl-3', 'lvl-4']],
            'across_specialties' => ['boolean', false],
            'across_all_levels' => ['boolean', false],
            'require_students' => ['boolean', false],
        ] as $name => [$type, $value]) {
            DB::table('setting_definations')->insert(['id' => "def-$name", 'name' => $name, 'key' => self::PREFIX . $name, 'data_type' => $type, 'setting_category_id' => 'cat']);
            foreach (['branch-1', 'branch-2'] as $branch) {
                SchoolBranchSetting::create(['school_branch_id' => $branch, 'setting_defination_id' => "def-$name", 'value' => $value]);
            }
        }
    }

    private function setting(string $name): SchoolBranchSetting
    {
        return SchoolBranchSetting::with('settingDefination')->where('school_branch_id', 'branch-1')->where('setting_defination_id', "def-$name")->first();
    }

    private function configure(string $mode, bool $acrossSpecialties = false, bool $acrossAllLevels = false, bool $requireStudents = false, ?array $levels = null): void
    {
        $this->setting('mode')->update(['value' => $mode]);
        $this->setting('across_specialties')->update(['value' => $acrossSpecialties]);
        $this->setting('across_all_levels')->update(['value' => $acrossAllLevels]);
        $this->setting('require_students')->update(['value' => $requireStudents]);
        if ($levels !== null) {
            $this->setting('levels')->update(['value' => $levels]);
        }
    }

    private function create(string $specialtyId = 'swe-1'): SchoolAcademicYear
    {
        return app(SchoolAcademicYearService::class)->createSchoolAcademicYear([
            'specialty_id' => $specialtyId,
            'system_academic_year_id' => 'year-2099',
            'start_date' => '2099-09-01',
            'end_date' => '2100-06-30',
        ], $this->school);
    }

    private function createdFor(): array
    {
        return SchoolAcademicYear::where('specialty_id', '!=', 'swe-1')->orderBy('specialty_id')->pluck('specialty_id')->all();
    }

    public function test_disabled_creates_only_the_selected_specialty_level(): void
    {
        $year = $this->create();

        $this->assertSame(1, SchoolAcademicYear::count());
        $this->assertCount(0, $year->autoCreatedAcademicYears);
    }

    public function test_within_the_same_specialty_creates_its_other_levels_with_fee_schedules_and_promotion_runs(): void
    {
        $this->configure('same_specialty');

        $year = $this->create();

        $this->assertSame(['swe-2', 'swe-3'], $this->createdFor());
        $this->assertCount(2, $year->autoCreatedAcademicYears);
        $this->assertDatabaseHas('school_academic_years', ['specialty_id' => 'swe-2', 'system_academic_year_id' => 'year-2099', 'start_date' => '2099-09-01', 'end_date' => '2100-06-30', 'school_branch_id' => 'branch-1']);
        $this->assertSame(3, FeeSchedule::count());
        $this->assertSame(3, PromotionRun::count());
    }

    public function test_across_specialties_uses_the_original_specialtys_levels(): void
    {
        $this->configure('across_specialties', acrossSpecialties: true);

        $this->create();

        // Networking L4 is skipped (Software Engineering has no Level 4); Accounting is another department.
        $this->assertSame(['net-2', 'swe-2', 'swe-3'], $this->createdFor());
    }

    public function test_across_all_specialties_levels_includes_every_applicable_level(): void
    {
        $this->configure('across_specialties', acrossAllLevels: true, levels: ['lvl-2', 'lvl-4']);

        $this->create();

        $this->assertSame(['net-2', 'net-4', 'swe-2'], $this->createdFor());
    }

    public function test_require_students_skips_specialty_levels_without_enrolled_students(): void
    {
        $this->configure('same_specialty', requireStudents: true);
        DB::table('students')->insert(['id' => 'stu-3', 'name' => 'S', 'first_name' => 'S', 'last_name' => 'S', 'email' => 's@example.com', 'password' => 'x', 'username' => 's', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-3', 'dropout_status' => false, 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);

        $this->create();

        $this->assertSame(['swe-3'], $this->createdFor());
    }

    public function test_existing_academic_years_are_not_duplicated_and_other_branches_are_untouched(): void
    {
        $this->configure('across_specialties', acrossSpecialties: true, acrossAllLevels: true);
        DB::table('school_academic_years')->insert(['id' => 'existing', 'start_date' => '2099-10-01', 'end_date' => '2100-05-30', 'specialty_id' => 'swe-2', 'system_academic_year_id' => 'year-2099', 'school_branch_id' => 'branch-1']);

        $this->create();

        $this->assertSame(1, SchoolAcademicYear::where('specialty_id', 'swe-2')->count());
        $this->assertSame(0, SchoolAcademicYear::where('school_branch_id', 'branch-2')->count());
    }

    public function test_a_failure_rolls_back_the_original_and_every_copy(): void
    {
        $this->configure('same_specialty');
        $this->app->instance(SchoolAcademicYearAutoCreationService::class, new class(app(\App\Services\AutoCreation\AutoCreationTargets::class)) extends SchoolAcademicYearAutoCreationService {
            public function createFor(SchoolAcademicYear $original, \App\Models\Specialty $specialty): Collection
            {
                parent::createFor($original, $specialty);
                throw new RuntimeException('boom');
            }
        });

        try {
            $this->create();
            $this->fail('Expected the creation to fail.');
        } catch (RuntimeException) {
        }

        $this->assertSame(0, SchoolAcademicYear::count());
        $this->assertSame(0, FeeSchedule::count());
    }

    public function test_a_specialty_from_another_branch_cannot_be_used(): void
    {
        $this->expectException(AppException::class);
        $this->expectExceptionCode(404);

        $this->create('b2-swe-2');
    }

    public function test_handlers_keep_the_academic_year_group_in_step_without_touching_semester_settings(): void
    {
        DB::table('setting_definations')->insert(['id' => 'def-sem-mode', 'name' => 'sem mode', 'key' => 'semester.auto_create_mode', 'data_type' => 'string', 'setting_category_id' => 'cat']);
        SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => 'def-sem-mode', 'value' => 'disabled']);

        try {
            (new AutoCreateRequireStudentsHandler())->handle($this->setting('require_students'), true, $this->school, null);
            $this->fail('Expected the setting to be refused while creation is disabled.');
        } catch (AppException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertStringContainsString('Automatic Academic Year Creation', $e->getMessage());
        }

        (new AutoCreateModeHandler())->handle($this->setting('mode'), 'across_specialties', $this->school, null);
        $this->assertTrue($this->setting('across_specialties')->value);

        (new AutoCreateAcrossSwitchHandler())->handle($this->setting('across_specialties'), false, $this->school, null);
        $this->assertSame('same_specialty', $this->setting('mode')->value);

        $this->assertSame('disabled', SchoolBranchSetting::where('setting_defination_id', 'def-sem-mode')->first()->value);
    }
}
