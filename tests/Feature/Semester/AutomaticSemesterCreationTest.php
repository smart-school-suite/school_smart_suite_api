<?php

namespace Tests\Feature\Semester;

use App\Exceptions\AppException;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\SchoolBranchSetting;
use App\Models\SchoolSemester;
use App\SchoolBranchSetting\Handlers\Semester\AutoCreateAcrossSwitchHandler;
use App\SchoolBranchSetting\Handlers\Semester\AutoCreateLevelsHandler;
use App\SchoolBranchSetting\Handlers\Semester\AutoCreateModeHandler;
use App\Services\Semester\SchoolSemesterService;
use App\Services\Semester\SemesterAutoCreationService;
use App\Services\Semester\SemesterAutoCreationSettings as Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class AutomaticSemesterCreationTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        $this->school = (object) ['id' => 'branch-1'];

        DB::table('school_branches')->insert([
            ['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1'],
            ['id' => 'branch-2', 'name' => 'Other', 'abbreviation' => 'O', 'school_id' => 'school-1'],
        ]);
        DB::table('system_academic_years')->insert([
            ['id' => 'year-2099', 'name' => '2099/2100', 'year_start' => 2099, 'year_end' => 2100],
            ['id' => 'year-2100', 'name' => '2100/2101', 'year_start' => 2100, 'year_end' => 2101],
        ]);
        DB::table('semesters')->insert(['id' => 'sem-1', 'name' => 'First Semester', 'count' => 1, 'program_name' => 'HND']);

        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100-Level Start', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200, 'lvl-3' => 300, 'lvl-4' => 400] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }

        // Computing department: Software Engineering L1-L3, Networking L2-L4.
        // Business department:  Accounting L2. Branch 2: Software Engineering L2.
        $this->specialty('swe-1', 'Software Engineering', 'lvl-1');
        $this->specialty('swe-2', 'Software Engineering', 'lvl-2');
        $this->specialty('swe-3', 'Software Engineering', 'lvl-3');
        $this->specialty('net-2', 'Networking', 'lvl-2');
        $this->specialty('net-3', 'Networking', 'lvl-3');
        $this->specialty('net-4', 'Networking', 'lvl-4');
        $this->specialty('acc-2', 'Accounting', 'lvl-2', 'business');
        $this->specialty('b2-swe-2', 'Software Engineering', 'lvl-2', 'computing', 'branch-2');

        foreach (['swe-1', 'swe-2', 'swe-3', 'net-2', 'net-3', 'net-4', 'acc-2'] as $id) {
            $this->schoolYear("sy-$id", $id);
        }
        $this->schoolYear('sy-b2-swe-2', 'b2-swe-2', 'branch-2');

        DB::table('setting_categories')->insert(['id' => 'cat-sem', 'name' => 'Semester Settings', 'key' => 'setting.category.semester']);
        $defaults = [
            Settings::MODE => ['string', 'disabled'],
            Settings::LEVELS => ['json', ['lvl-1', 'lvl-2', 'lvl-3', 'lvl-4']],
            Settings::ACROSS_SPECIALTIES => ['boolean', false],
            Settings::ACROSS_ALL_LEVELS => ['boolean', false],
            'level.start' => ['string', 'level_start_100'],
        ];
        foreach ($defaults as $key => [$type, $value]) {
            DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat-sem']);
            foreach (['branch-1', 'branch-2'] as $branch) {
                SchoolBranchSetting::create(['school_branch_id' => $branch, 'setting_defination_id' => "def-$key", 'value' => $value]);
            }
        }
    }

    private function specialty(string $id, string $name, string $levelId, string $department = 'computing', string $branch = 'branch-1'): void
    {
        DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => $branch, 'department_id' => $department, 'level_id' => $levelId]);
    }

    private function schoolYear(string $id, string $specialtyId, string $branch = 'branch-1', string $year = 'year-2099'): void
    {
        DB::table('school_academic_years')->insert(['id' => $id, 'start_date' => '2099-09-01', 'end_date' => '2100-06-30', 'specialty_id' => $specialtyId, 'system_academic_year_id' => $year, 'school_branch_id' => $branch]);
    }

    private function setting(string $key): SchoolBranchSetting
    {
        return SchoolBranchSetting::with('settingDefination')->where('school_branch_id', 'branch-1')->where('setting_defination_id', "def-$key")->first();
    }

    private function value(string $key): mixed
    {
        return $this->setting($key)->value;
    }

    private function configure(string $mode, bool $acrossSpecialties = false, bool $acrossAllLevels = false, ?array $levels = null): void
    {
        $this->setting(Settings::MODE)->update(['value' => $mode]);
        $this->setting(Settings::ACROSS_SPECIALTIES)->update(['value' => $acrossSpecialties]);
        $this->setting(Settings::ACROSS_ALL_LEVELS)->update(['value' => $acrossAllLevels]);
        if ($levels !== null) {
            $this->setting(Settings::LEVELS)->update(['value' => $levels]);
        }
    }

    private function create(string $schoolYearId = 'sy-swe-1'): SchoolSemester
    {
        return app(SchoolSemesterService::class)->createSchoolSemester([
            'start_date' => '2099-09-15',
            'end_date' => '2100-01-31',
            'school_year_id' => $schoolYearId,
            'semester_id' => 'sem-1',
        ], $this->school, null);
    }

    private function createdFor(): array
    {
        return SchoolSemester::where('school_year_id', '!=', 'sy-swe-1')->orderBy('school_year_id')->pluck('school_year_id')->all();
    }

    public function test_disabled_creates_only_the_selected_level(): void
    {
        $semester = $this->create();

        $this->assertSame(1, SchoolSemester::count());
        $this->assertCount(0, $semester->autoCreatedSemesters);
    }

    public function test_within_the_same_specialty_creates_its_other_applicable_levels(): void
    {
        $this->configure('same_specialty');

        $semester = $this->create();

        $this->assertSame(['sy-swe-2', 'sy-swe-3'], $this->createdFor());
        $this->assertCount(2, $semester->autoCreatedSemesters);
        $this->assertDatabaseHas('school_semesters', [
            'school_year_id' => 'sy-swe-2', 'semester_id' => 'sem-1', 'school_branch_id' => 'branch-1',
            'start_date' => '2099-09-15', 'end_date' => '2100-01-31', 'timetable_published' => false,
        ]);
    }

    public function test_across_specialties_adds_other_specialties_at_the_original_specialtys_levels(): void
    {
        $this->configure('across_specialties', acrossSpecialties: true);

        $this->create();

        // Networking L4 is skipped: Software Engineering has no Level 4.
        // Accounting is in another department.
        $this->assertSame(['sy-net-2', 'sy-net-3', 'sy-swe-2', 'sy-swe-3'], $this->createdFor());
    }

    public function test_across_all_specialties_levels_includes_levels_the_original_specialty_lacks(): void
    {
        $this->configure('across_specialties', acrossAllLevels: true);

        $this->create();

        $this->assertSame(['sy-net-2', 'sy-net-3', 'sy-net-4', 'sy-swe-2', 'sy-swe-3'], $this->createdFor());
    }

    public function test_both_switches_can_be_on_together(): void
    {
        $this->configure('across_specialties', acrossSpecialties: true, acrossAllLevels: true);

        $this->create();

        $this->assertSame(['sy-net-2', 'sy-net-3', 'sy-net-4', 'sy-swe-2', 'sy-swe-3'], $this->createdFor());
    }

    public function test_switches_are_ignored_while_disabled_or_within_the_same_specialty(): void
    {
        $this->configure('disabled', acrossSpecialties: true, acrossAllLevels: true);
        $this->create();
        $this->assertSame([], $this->createdFor());

        SchoolSemester::query()->delete();
        $this->configure('same_specialty', acrossSpecialties: true, acrossAllLevels: true);
        $this->create();
        $this->assertSame(['sy-swe-2', 'sy-swe-3'], $this->createdFor());
    }

    public function test_only_applicable_levels_receive_copies_but_the_original_level_always_does(): void
    {
        $this->configure('across_specialties', acrossAllLevels: true, levels: ['lvl-3', 'lvl-4']);

        $this->create();

        $this->assertSame(['sy-net-3', 'sy-net-4', 'sy-swe-3'], $this->createdFor());
        $this->assertDatabaseHas('school_semesters', ['school_year_id' => 'sy-swe-1']);
    }

    public function test_levels_without_the_selected_academic_year_are_skipped_and_none_is_created(): void
    {
        $this->configure('across_specialties', acrossAllLevels: true);
        DB::table('school_academic_years')->where('id', 'sy-swe-3')->delete();
        DB::table('school_academic_years')->where('id', 'sy-net-4')->update(['system_academic_year_id' => 'year-2100']);

        $this->create();

        $this->assertSame(['sy-net-2', 'sy-net-3', 'sy-swe-2'], $this->createdFor());
        $this->assertSame(0, SchoolAcademicYear::where('specialty_id', 'swe-3')->count());
    }

    public function test_existing_semesters_are_not_duplicated(): void
    {
        $this->configure('same_specialty');
        DB::table('school_semesters')->insert(['id' => 'existing', 'start_date' => '2099-10-01', 'end_date' => '2100-02-01', 'school_year_id' => 'sy-swe-2', 'semester_id' => 'sem-1', 'school_branch_id' => 'branch-1', 'timetable_published' => false]);

        $this->create();

        $this->assertSame(1, SchoolSemester::where('school_year_id', 'sy-swe-2')->count());
        $this->assertSame(1, SchoolSemester::where('school_year_id', 'sy-swe-3')->count());
    }

    public function test_creation_never_leaves_the_school_branch(): void
    {
        $this->configure('across_specialties', acrossSpecialties: true, acrossAllLevels: true);

        $this->create();

        $this->assertSame(0, SchoolSemester::where('school_branch_id', 'branch-2')->count());
    }

    public function test_a_failure_rolls_back_the_original_and_every_copy(): void
    {
        $this->configure('same_specialty');
        $this->app->instance(SemesterAutoCreationService::class, new class extends SemesterAutoCreationService {
            public function createFor(SchoolSemester $original, SchoolAcademicYear $originalYear): Collection
            {
                parent::createFor($original, $originalYear);
                throw new RuntimeException('boom');
            }
        });

        try {
            $this->create();
            $this->fail('Expected the creation to fail.');
        } catch (AppException $e) {
            $this->assertSame(500, $e->getCode());
        }

        $this->assertSame(0, SchoolSemester::count());
    }

    public function test_later_edits_and_deletions_are_not_synchronized(): void
    {
        $this->configure('same_specialty');
        $semester = $this->create();
        $service = app(SchoolSemesterService::class);

        $service->updateSchoolSemester(['end_date' => '2100-02-15'], $this->school, $semester->id, null);
        $this->assertDatabaseHas('school_semesters', ['school_year_id' => 'sy-swe-2', 'end_date' => '2100-01-31']);

        $service->deleteSchoolSemester($semester->id, $this->school, null);
        $this->assertSame(['sy-swe-2', 'sy-swe-3'], $this->createdFor());
    }

    public function test_mode_handler_keeps_the_switches_in_step(): void
    {
        $handler = new AutoCreateModeHandler();

        $handler->handle($this->setting(Settings::MODE), 'across_specialties', $this->school, null);
        $this->assertTrue($this->value(Settings::ACROSS_SPECIALTIES));
        $this->assertFalse($this->value(Settings::ACROSS_ALL_LEVELS));

        $handler->handle($this->setting(Settings::MODE), 'same_specialty', $this->school, null);
        $this->assertFalse($this->value(Settings::ACROSS_SPECIALTIES));

        $this->expectException(AppException::class);
        $handler->handle($this->setting(Settings::MODE), 'everywhere', $this->school, null);
    }

    public function test_switches_need_creation_enabled_and_keep_the_mode_in_step(): void
    {
        $handler = new AutoCreateAcrossSwitchHandler();

        try {
            $handler->handle($this->setting(Settings::ACROSS_ALL_LEVELS), true, $this->school, null);
            $this->fail('Expected the switch to be refused while creation is disabled.');
        } catch (AppException $e) {
            $this->assertSame(400, $e->getCode());
        }

        $this->configure('same_specialty');
        $handler->handle($this->setting(Settings::ACROSS_ALL_LEVELS), true, $this->school, null);
        $handler->handle($this->setting(Settings::ACROSS_SPECIALTIES), true, $this->school, null);
        $this->assertSame('across_specialties', $this->value(Settings::MODE));
        $this->assertTrue($this->value(Settings::ACROSS_SPECIALTIES));
        $this->assertTrue($this->value(Settings::ACROSS_ALL_LEVELS));

        $handler->handle($this->setting(Settings::ACROSS_SPECIALTIES), false, $this->school, null);
        $this->assertSame('across_specialties', $this->value(Settings::MODE));

        $handler->handle($this->setting(Settings::ACROSS_ALL_LEVELS), false, $this->school, null);
        $this->assertSame('same_specialty', $this->value(Settings::MODE));
    }

    public function test_levels_handler_accepts_school_levels_and_rejects_others(): void
    {
        $handler = new AutoCreateLevelsHandler();
        DB::table('level_types')->insert(['id' => 'type-200', 'name' => '200-Level Start', 'program_name' => 'level_start_200', 'description' => '-']);
        DB::table('levels')->insert(['id' => 'other-type', 'name' => 'Level 200', 'level' => 200, 'program_name' => 'x', 'level_type_id' => 'type-200']);

        $this->assertSame(['lvl-2', 'lvl-3'], $handler->handle($this->setting(Settings::LEVELS), ['lvl-2', 'lvl-2', 'lvl-3'], $this->school, null)->value);
        $this->assertSame([], $handler->handle($this->setting(Settings::LEVELS), [], $this->school, null)->value);

        foreach ([['other-type'], ['missing'], 'lvl-1', ['a' => 'lvl-1']] as $bad) {
            try {
                $handler->handle($this->setting(Settings::LEVELS), $bad, $this->school, null);
                $this->fail('Expected the levels to be rejected.');
            } catch (AppException $e) {
                $this->assertSame(400, $e->getCode());
            }
        }
    }
}
