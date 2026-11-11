<?php

namespace Tests\Feature\SchoolBranchSetting;

use App\Exceptions\AppException;
use App\Jobs\Promotion\StudentGraduationJob;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Handlers\Graduation\LevelGraduationFeeBillingHandler;
use App\SchoolBranchSetting\Handlers\SemesterTimetable\IgnoreTeacherPreferenceHandler;
use App\SchoolBranchSetting\Handlers\SemesterTimetable\RespectTeacherPreferenceHandler;
use App\SchoolBranchSetting\Handlers\SemesterTimetable\SchoolDayHoursHandler;
use App\SchoolBranchSetting\Registry\SettingUpdateRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

class MutuallyExclusiveSettingsTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->school = (object) ['id' => 'branch-1'];

        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Settings', 'key' => 'setting.category.test']);
        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100-Level Start', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }

        foreach ([
            'timetable.ignore_teacher_preference' => ['boolean', false],
            'timetable.respect_teacher_preference' => ['boolean', true],
            'graduation.fee' => ['decimal', 50000],
            'graduation.level_graduation_fee_enabled' => ['boolean', false],
            'graduation.level_graduation_fee' => ['json', '[]'],
            'level.start' => ['string', 'level_start_100'],
            'timetable.school_day_start' => ['string', '08:00'],
            'timetable.school_day_end' => ['string', '17:00'],
        ] as $key => [$type, $value]) {
            DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat']);
            SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$key", 'value' => $value]);
        }
    }

    private function setting(string $key): SchoolBranchSetting
    {
        return SchoolBranchSetting::with('settingDefination')->where('setting_defination_id', "def-$key")->first();
    }

    public function test_exactly_one_teacher_preference_option_is_on(): void
    {
        $ignore = new IgnoreTeacherPreferenceHandler();
        $respect = new RespectTeacherPreferenceHandler();

        $ignore->handle($this->setting('timetable.ignore_teacher_preference'), true, $this->school, null);
        $this->assertTrue($this->setting('timetable.ignore_teacher_preference')->value);
        $this->assertFalse($this->setting('timetable.respect_teacher_preference')->value);

        // Turning the active option off turns the other one back on.
        $ignore->handle($this->setting('timetable.ignore_teacher_preference'), false, $this->school, null);
        $this->assertTrue($this->setting('timetable.respect_teacher_preference')->value);

        $respect->handle($this->setting('timetable.respect_teacher_preference'), false, $this->school, null);
        $this->assertTrue($this->setting('timetable.ignore_teacher_preference')->value);
        $this->assertFalse($this->setting('timetable.respect_teacher_preference')->value);
    }

    public function test_per_level_graduation_fees_are_saved_by_the_level_fee_handler(): void
    {
        $this->assertSame(
            LevelGraduationFeeBillingHandler::class,
            app(SettingUpdateRegistry::class)->handlers()['graduation.level_graduation_fee']
        );

        $fees = [['level_id' => 'lvl-1', 'fee' => 40000], ['level_id' => 'lvl-2', 'fee' => 60000]];
        $saved = (new LevelGraduationFeeBillingHandler())->handle($this->setting('graduation.level_graduation_fee'), $fees, $this->school, null);
        $this->assertSame($fees, $saved->value);

        $this->expectException(AppException::class);
        (new LevelGraduationFeeBillingHandler())->handle($this->setting('graduation.level_graduation_fee'), [['level_id' => 'lvl-1', 'fee' => 40000]], $this->school, null);
    }

    public function test_graduation_fee_uses_the_level_fee_only_when_per_level_fees_are_on(): void
    {
        $amount = fn(?string $levelId) => (new ReflectionMethod(StudentGraduationJob::class, 'graduationFeeAmount'))
            ->invoke(new StudentGraduationJob('run', 'branch-1'), 'branch-1', $levelId);

        $this->setting('graduation.level_graduation_fee')->update(['value' => [['level_id' => 'lvl-2', 'fee' => 60000]]]);
        $this->assertSame(50000.0, $amount('lvl-2'));

        $this->setting('graduation.level_graduation_fee_enabled')->update(['value' => true]);
        $this->assertSame(60000.0, $amount('lvl-2'));
        $this->assertSame(50000.0, $amount('lvl-1'), 'A level without its own fee falls back to the standard fee.');
        $this->assertSame(50000.0, $amount(null));
    }

    public function test_the_school_day_must_open_before_it_closes(): void
    {
        $handler = new SchoolDayHoursHandler();

        $this->assertSame('16:30', $handler->handle($this->setting('timetable.school_day_end'), '16:30', $this->school, null)->value);

        foreach ([['timetable.school_day_end', '07:00'], ['timetable.school_day_start', '18:00'], ['timetable.school_day_start', '8am']] as [$key, $time]) {
            try {
                $handler->handle($this->setting($key), $time, $this->school, null);
                $this->fail("Expected $key = $time to be rejected.");
            } catch (AppException $e) {
                $this->assertSame(400, $e->getCode());
            }
        }
        $this->assertSame('08:00', $this->setting('timetable.school_day_start')->value);
    }
}
