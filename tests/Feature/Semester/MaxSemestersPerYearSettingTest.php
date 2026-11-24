<?php

namespace Tests\Feature\Semester;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Resolvers\SettingUpdateResolver;
use App\Services\Semester\SemesterLimit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SetsSemesterLimit;
use Tests\TestCase;

class MaxSemestersPerYearSettingTest extends TestCase
{
    use RefreshDatabase;
    use SetsSemesterLimit;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        foreach ([1, 2, 3, 4] as $count) {
            DB::table('semesters')->insert(['id' => "sem-$count", 'name' => "Semester $count", 'count' => $count, 'program_name' => 'HND']);
        }
        $this->setSemesterLimit(2);
    }

    private function save(mixed $value): SchoolBranchSetting
    {
        $setting = SchoolBranchSetting::where('setting_defination_id', 'def-sem-limit')->first();

        return app(SettingUpdateResolver::class)->resolve(SemesterLimit::KEY)
            ->handle($setting, $value, (object) ['id' => 'branch-1'], null);
    }

    public function test_it_saves_a_whole_number_within_the_available_semesters(): void
    {
        $this->assertSame(3, (int) $this->save('3')->value);
        $this->assertSame(3, app(SemesterLimit::class)->forBranch('branch-1'));
    }

    public function test_it_refuses_values_outside_the_range(): void
    {
        foreach ([0, 5, 2.5, 'two'] as $value) {
            try {
                $this->save($value);
                $this->fail('Accepted ' . json_encode($value));
            } catch (AppException $e) {
                $this->assertSame(400, $e->getCode());
            }
        }
        $this->assertSame(2, app(SemesterLimit::class)->forBranch('branch-1'));
    }

    public function test_a_branch_without_the_setting_defaults_to_two(): void
    {
        $this->assertSame(2, app(SemesterLimit::class)->forBranch('branch-other'));
    }
}
