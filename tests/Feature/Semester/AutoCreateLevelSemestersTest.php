<?php

namespace Tests\Feature\Semester;

use App\Models\SchoolBranchSetting;
use App\Models\SchoolSemester;
use App\SchoolBranchSetting\Handlers\Semester\AutoCreateSemesterHandler;
use App\Services\Semester\SchoolSemesterService;
use App\Services\Semester\SemesterAutoCreateSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AutoCreateLevelSemestersTest extends TestCase
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

        // Software Engineering at three levels, plus an unrelated specialty.
        $this->specialty('swe-1', 'Software Engineering', 'lvl-1');
        $this->specialty('swe-2', 'Software Engineering', 'lvl-2');
        $this->specialty('swe-3', 'Software Engineering', 'lvl-3');
        $this->specialty('acc-2', 'Accounting', 'lvl-2');

        foreach (['swe-1', 'swe-2', 'swe-3', 'acc-2'] as $id) {
            $this->schoolYear("sy-$id", $id);
        }

        DB::table('setting_categories')->insert(['id' => 'cat-sem', 'name' => 'Semester Settings', 'key' => 'setting.category.semester']);
        DB::table('setting_definations')->insert([
            ['id' => 'def-all', 'name' => 'All levels', 'key' => SemesterAutoCreateSetting::ALL_LEVELS, 'data_type' => 'boolean', 'setting_category_id' => 'cat-sem'],
            ['id' => 'def-students', 'name' => 'Levels with students', 'key' => SemesterAutoCreateSetting::LEVELS_WITH_STUDENTS, 'data_type' => 'boolean', 'setting_category_id' => 'cat-sem'],
        ]);
        SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => 'def-all', 'value' => false]);
        SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => 'def-students', 'value' => false]);
    }

    private function specialty(string $id, string $name, string $levelId, string $status = 'active'): void
    {
        DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'registration_fee' => 0, 'school_fee' => 0, 'status' => $status, 'school_branch_id' => 'branch-1', 'department_id' => 'dep', 'level_id' => $levelId]);
    }

    private function schoolYear(string $id, string $specialtyId, string $start = '2099-09-01', string $end = '2100-06-30'): void
    {
        DB::table('school_academic_years')->insert(['id' => $id, 'start_date' => $start, 'end_date' => $end, 'specialty_id' => $specialtyId, 'system_academic_year_id' => 'year-2099', 'school_branch_id' => 'branch-1']);
    }

    private function student(string $id, string $specialtyId, bool $dropout = false): void
    {
        DB::table('students')->insert(['id' => $id, 'name' => $id, 'first_name' => $id, 'last_name' => 'S', 'email' => "$id@example.com", 'password' => 'x', 'username' => $id, 'school_branch_id' => 'branch-1', 'specialty_id' => $specialtyId, 'dropout_status' => $dropout, 'guardian_id' => 'g-1', 'student_batch_id' => 'batch-1', 'gender_id' => 'gen-1', 'relationship_id' => 'rel-1', 'student_source_id' => 'src-1']);
    }

    private function enable(string $key): void
    {
        SchoolBranchSetting::where('school_branch_id', 'branch-1')
            ->whereHas('settingDefination', fn($q) => $q->where('key', $key))
            ->first()
            ->update(['value' => true]);
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

    private function semesterYears(): array
    {
        return SchoolSemester::orderBy('school_year_id')->pluck('school_year_id')->all();
    }

    public function test_nothing_is_auto_created_when_both_settings_are_off(): void
    {
        $this->createLevelOneSemester();

        $this->assertSame(['sy-swe-1'], $this->semesterYears());
    }

    public function test_all_levels_mode_creates_the_semester_for_every_other_level_of_the_specialty(): void
    {
        $this->enable(SemesterAutoCreateSetting::ALL_LEVELS);

        $this->createLevelOneSemester();

        $this->assertSame(['sy-swe-1', 'sy-swe-2', 'sy-swe-3'], $this->semesterYears());
        $this->assertDatabaseHas('school_semesters', [
            'school_year_id' => 'sy-swe-2', 'semester_id' => 'sem-1',
            'start_date' => '2099-09-15', 'end_date' => '2100-01-31', 'timetable_published' => false,
        ]);
    }

    public function test_levels_with_students_mode_skips_levels_without_active_students(): void
    {
        $this->enable(SemesterAutoCreateSetting::LEVELS_WITH_STUDENTS);
        $this->student('stu-2', 'swe-2');
        $this->student('stu-3', 'swe-3', dropout: true);

        $this->createLevelOneSemester();

        $this->assertSame(['sy-swe-1', 'sy-swe-2'], $this->semesterYears());
    }

    public function test_levels_without_a_school_year_or_with_an_existing_semester_are_skipped(): void
    {
        $this->enable(SemesterAutoCreateSetting::ALL_LEVELS);
        DB::table('school_academic_years')->where('id', 'sy-swe-3')->delete();
        DB::table('school_semesters')->insert(['id' => 'existing', 'start_date' => '2099-10-01', 'end_date' => '2100-02-01', 'school_year_id' => 'sy-swe-2', 'semester_id' => 'sem-1', 'school_branch_id' => 'branch-1', 'timetable_published' => false]);

        $this->createLevelOneSemester();

        $this->assertSame(1, SchoolSemester::where('school_year_id', 'sy-swe-2')->count());
        $this->assertSame(0, SchoolSemester::where('school_year_id', 'sy-swe-3')->count());
    }

    public function test_a_level_whose_school_year_does_not_cover_the_dates_is_skipped(): void
    {
        $this->enable(SemesterAutoCreateSetting::ALL_LEVELS);
        DB::table('school_academic_years')->where('id', 'sy-swe-3')->update(['start_date' => '2099-10-01']);

        $this->createLevelOneSemester();

        $this->assertSame(['sy-swe-1', 'sy-swe-2'], $this->semesterYears());
    }

    public function test_inactive_level_specialties_are_skipped(): void
    {
        $this->enable(SemesterAutoCreateSetting::ALL_LEVELS);
        DB::table('specialties')->where('id', 'swe-3')->update(['status' => 'inactive']);

        $this->createLevelOneSemester();

        $this->assertSame(['sy-swe-1', 'sy-swe-2'], $this->semesterYears());
    }

    public function test_enabling_one_mode_switches_the_other_off(): void
    {
        $handler = new AutoCreateSemesterHandler();
        $all = SchoolBranchSetting::with('settingDefination')->where('setting_defination_id', 'def-all')->first();
        $students = SchoolBranchSetting::with('settingDefination')->where('setting_defination_id', 'def-students')->first();

        $handler->handle($all, 'true', $this->school, null);
        $this->assertSame(SemesterAutoCreateSetting::ALL_LEVELS, app(SemesterAutoCreateSetting::class)->modeFor('branch-1'));

        $handler->handle($students->fresh('settingDefination'), true, $this->school, null);
        $this->assertFalse(filter_var($all->fresh()->value, FILTER_VALIDATE_BOOLEAN));
        $this->assertSame(SemesterAutoCreateSetting::LEVELS_WITH_STUDENTS, app(SemesterAutoCreateSetting::class)->modeFor('branch-1'));

        $handler->handle($students->fresh('settingDefination'), false, $this->school, null);
        $this->assertNull(app(SemesterAutoCreateSetting::class)->modeFor('branch-1'));
    }
}
