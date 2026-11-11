<?php

namespace Tests\Feature\Semester;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\Models\SchoolSemester;
use App\SchoolBranchSetting\Handlers\Semester\SemesterSyncAllowedLevelsHandler;
use App\SchoolBranchSetting\Handlers\Semester\SemesterSyncModeHandler;
use App\SchoolBranchSetting\Handlers\Semester\SemesterSyncScopeHandler;
use App\Services\Semester\SchoolSemesterService;
use App\Services\Semester\SemesterSyncSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SynchronizeSemesterTest extends TestCase
{
    use RefreshDatabase;

    private object $school;
    private SchoolSemesterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        $this->school = (object) ['id' => 'branch-1'];
        $this->service = app(SchoolSemesterService::class);

        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        DB::table('system_academic_years')->insert(['id' => 'year-2099', 'name' => '2099/2100', 'year_start' => 2099, 'year_end' => 2100]);
        DB::table('semesters')->insert(['id' => 'sem-1', 'name' => 'First Semester', 'count' => 1, 'program_name' => 'HND']);

        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100-Level Start', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200, 'lvl-3' => 300] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }

        // Department "computing": Software Engineering L1-L3 and Networking L2.
        // Department "business":  Software Engineering L2 (same name, other department) and Accounting L2.
        $this->specialty('swe-1', 'Software Engineering', 'lvl-1', 'computing');
        $this->specialty('swe-2', 'Software Engineering', 'lvl-2', 'computing');
        $this->specialty('swe-3', 'Software Engineering', 'lvl-3', 'computing');
        $this->specialty('net-2', 'Networking', 'lvl-2', 'computing');
        $this->specialty('bswe-2', 'Software Engineering', 'lvl-2', 'business');
        $this->specialty('acc-2', 'Accounting', 'lvl-2', 'business');

        foreach (['swe-1', 'swe-2', 'swe-3', 'net-2', 'bswe-2', 'acc-2'] as $id) {
            DB::table('school_academic_years')->insert(['id' => "sy-$id", 'start_date' => '2099-09-01', 'end_date' => '2100-06-30', 'specialty_id' => $id, 'system_academic_year_id' => 'year-2099', 'school_branch_id' => 'branch-1']);
        }

        DB::table('setting_categories')->insert(['id' => 'cat-sem', 'name' => 'Semester Settings', 'key' => 'setting.category.semester']);
        $defaults = [
            SemesterSyncSettings::MODE => ['string', 'disabled'],
            SemesterSyncSettings::ALLOWED_LEVELS => ['json', ['lvl-1', 'lvl-2', 'lvl-3']],
            SemesterSyncSettings::SCOPE => ['string', 'same_specialty'],
            SemesterSyncSettings::REQUIRE_STUDENTS => ['boolean', false],
            'level.start' => ['string', 'level_start_100'],
        ];
        foreach ($defaults as $key => [$type, $value]) {
            DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat-sem']);
            SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$key", 'value' => $value]);
        }
    }

    private function specialty(string $id, string $name, string $levelId, string $departmentId, string $status = 'active'): void
    {
        DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'registration_fee' => 0, 'school_fee' => 0, 'status' => $status, 'school_branch_id' => 'branch-1', 'department_id' => $departmentId, 'level_id' => $levelId]);
    }

    private function student(string $id, string $specialtyId, bool $dropout = false): void
    {
        DB::table('students')->insert(['id' => $id, 'name' => $id, 'first_name' => $id, 'last_name' => 'S', 'email' => "$id@example.com", 'password' => 'x', 'username' => $id, 'school_branch_id' => 'branch-1', 'specialty_id' => $specialtyId, 'dropout_status' => $dropout, 'guardian_id' => 'g-1', 'student_batch_id' => 'batch-1', 'gender_id' => 'gen-1', 'relationship_id' => 'rel-1', 'student_source_id' => 'src-1']);
    }

    private function setting(string $key): SchoolBranchSetting
    {
        return SchoolBranchSetting::with('settingDefination')->where('setting_defination_id', "def-$key")->first();
    }

    private function set(string $key, mixed $value): void
    {
        $this->setting($key)->update(['value' => $value]);
    }

    private function createLevelOneSemester(): SchoolSemester
    {
        return $this->service->createSchoolSemester([
            'start_date' => '2099-09-15',
            'end_date' => '2100-01-31',
            'school_year_id' => 'sy-swe-1',
            'semester_id' => 'sem-1',
        ], $this->school, null);
    }

    private function synchronizedTo(): array
    {
        return SchoolSemester::where('school_year_id', '!=', 'sy-swe-1')->orderBy('school_year_id')->pluck('school_year_id')->all();
    }

    public function test_disabled_mode_creates_only_the_selected_level(): void
    {
        $this->createLevelOneSemester();

        $this->assertSame(1, SchoolSemester::count());
    }

    public function test_same_programme_creates_the_semester_for_the_specialtys_other_levels(): void
    {
        $this->set(SemesterSyncSettings::MODE, 'same_programme');

        $this->createLevelOneSemester();

        $this->assertSame(['sy-swe-2', 'sy-swe-3'], $this->synchronizedTo());
        $this->assertDatabaseHas('school_semesters', [
            'school_year_id' => 'sy-swe-2', 'semester_id' => 'sem-1',
            'start_date' => '2099-09-15', 'end_date' => '2100-01-31', 'timetable_published' => false,
        ]);
    }

    public function test_across_programmes_same_specialty_includes_the_same_name_in_other_departments(): void
    {
        $this->set(SemesterSyncSettings::MODE, 'across_programmes');
        $this->set(SemesterSyncSettings::SCOPE, 'same_specialty');

        $this->createLevelOneSemester();

        $this->assertSame(['sy-bswe-2', 'sy-swe-2', 'sy-swe-3'], $this->synchronizedTo());
    }

    public function test_across_programmes_across_specialties_covers_the_department(): void
    {
        $this->set(SemesterSyncSettings::MODE, 'across_programmes');
        $this->set(SemesterSyncSettings::SCOPE, 'across_specialties');

        $this->createLevelOneSemester();

        $this->assertSame(['sy-net-2', 'sy-swe-2', 'sy-swe-3'], $this->synchronizedTo());
    }

    public function test_across_programmes_entire_institution_covers_every_specialty(): void
    {
        $this->set(SemesterSyncSettings::MODE, 'across_programmes');
        $this->set(SemesterSyncSettings::SCOPE, 'institution');

        $this->createLevelOneSemester();

        $this->assertSame(['sy-acc-2', 'sy-bswe-2', 'sy-net-2', 'sy-swe-2', 'sy-swe-3'], $this->synchronizedTo());
    }

    public function test_scope_is_ignored_in_same_programme_mode(): void
    {
        $this->set(SemesterSyncSettings::MODE, 'same_programme');
        $this->set(SemesterSyncSettings::SCOPE, 'institution');

        $this->createLevelOneSemester();

        $this->assertSame(['sy-swe-2', 'sy-swe-3'], $this->synchronizedTo());
    }

    public function test_only_allowed_levels_receive_the_semester_but_the_source_level_always_does(): void
    {
        $this->set(SemesterSyncSettings::MODE, 'same_programme');
        $this->set(SemesterSyncSettings::ALLOWED_LEVELS, ['lvl-3']);

        $this->createLevelOneSemester();

        $this->assertSame(['sy-swe-3'], $this->synchronizedTo());
        $this->assertDatabaseHas('school_semesters', ['school_year_id' => 'sy-swe-1']);
    }

    public function test_seeded_allowed_levels_stored_as_a_json_string_are_read(): void
    {
        $this->set(SemesterSyncSettings::MODE, 'same_programme');
        $this->set(SemesterSyncSettings::ALLOWED_LEVELS, json_encode(['lvl-2']));

        $this->createLevelOneSemester();

        $this->assertSame(['sy-swe-2'], $this->synchronizedTo());
    }

    public function test_require_students_skips_levels_without_active_students(): void
    {
        $this->set(SemesterSyncSettings::MODE, 'same_programme');
        $this->set(SemesterSyncSettings::REQUIRE_STUDENTS, true);
        $this->student('stu-2', 'swe-2');
        $this->student('stu-3', 'swe-3', dropout: true);

        $this->createLevelOneSemester();

        $this->assertSame(['sy-swe-2'], $this->synchronizedTo());
    }

    public function test_targets_without_a_covering_school_year_existing_semester_or_inactive_are_skipped(): void
    {
        $this->set(SemesterSyncSettings::MODE, 'across_programmes');
        $this->set(SemesterSyncSettings::SCOPE, 'institution');
        DB::table('school_academic_years')->where('id', 'sy-swe-3')->delete();
        DB::table('school_academic_years')->where('id', 'sy-net-2')->update(['start_date' => '2099-10-01']);
        DB::table('specialties')->where('id', 'acc-2')->update(['status' => 'inactive']);
        DB::table('school_semesters')->insert(['id' => 'existing', 'start_date' => '2099-10-01', 'end_date' => '2100-02-01', 'school_year_id' => 'sy-swe-2', 'semester_id' => 'sem-1', 'school_branch_id' => 'branch-1', 'timetable_published' => false]);

        $this->createLevelOneSemester();

        $this->assertSame(['sy-bswe-2', 'sy-swe-2'], $this->synchronizedTo());
        $this->assertSame(1, SchoolSemester::where('school_year_id', 'sy-swe-2')->count());
    }

    public function test_mode_and_scope_handlers_reject_unknown_values(): void
    {
        foreach ([
            [new SemesterSyncModeHandler(), SemesterSyncSettings::MODE, 'everywhere'],
            [new SemesterSyncScopeHandler(), SemesterSyncSettings::SCOPE, 'faculty'],
        ] as [$handler, $key, $value]) {
            try {
                $handler->handle($this->setting($key), $value, $this->school, null);
                $this->fail("Expected $key to reject $value.");
            } catch (AppException $e) {
                $this->assertSame(400, $e->getCode());
            }
        }

        $updated = (new SemesterSyncModeHandler())->handle($this->setting(SemesterSyncSettings::MODE), 'across_programmes', $this->school, null);
        $this->assertSame('across_programmes', $updated->value);
    }

    public function test_allowed_levels_handler_accepts_school_levels_and_rejects_others(): void
    {
        $handler = new SemesterSyncAllowedLevelsHandler();
        DB::table('level_types')->insert(['id' => 'type-200', 'name' => '200-Level Start', 'program_name' => 'level_start_200', 'description' => '-']);
        DB::table('levels')->insert(['id' => 'other-type', 'name' => 'Level 200', 'level' => 200, 'program_name' => 'x', 'level_type_id' => 'type-200']);

        $updated = $handler->handle($this->setting(SemesterSyncSettings::ALLOWED_LEVELS), ['lvl-2', 'lvl-2', 'lvl-3'], $this->school, null);
        $this->assertSame(['lvl-2', 'lvl-3'], $updated->value);

        $this->assertSame([], $handler->handle($this->setting(SemesterSyncSettings::ALLOWED_LEVELS), [], $this->school, null)->value);

        foreach ([['other-type'], ['missing'], 'lvl-1', ['a' => 'lvl-1']] as $bad) {
            try {
                $handler->handle($this->setting(SemesterSyncSettings::ALLOWED_LEVELS), $bad, $this->school, null);
                $this->fail('Expected the allowed levels to be rejected.');
            } catch (AppException $e) {
                $this->assertSame(400, $e->getCode());
            }
        }
    }
}
