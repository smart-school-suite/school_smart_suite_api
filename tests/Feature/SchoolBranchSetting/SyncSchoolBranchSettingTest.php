<?php

namespace Tests\Feature\SchoolBranchSetting;

use App\Models\SchoolBranchSetting;
use App\Models\SettingDefination;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SettingCategorySeeder;
use Database\Seeders\SettingDefinationSeeder;
use Database\Seeders\SyncSchoolBranchSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Settings defined after a school subscribed reach it on the next deploy's db:seed. */
class SyncSchoolBranchSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new SettingCategorySeeder())->run();
        (new SettingDefinationSeeder())->run();

        foreach (['branch-1', 'branch-2'] as $id) {
            DB::table('school_branches')->insert(['id' => $id, 'school_id' => 'school-1', 'name' => $id, 'abbreviation' => strtoupper($id)]);
        }
        // branch-1 subscribed before the due-period setting existed, and chose its own passing GPA.
        $passingGpa = SettingDefination::where('key', 'grade.passing_gpa')->value('id');
        SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => $passingGpa, 'value' => 2.5]);
    }

    private function sync(): void
    {
        (new SyncSchoolBranchSettingSeeder())->run();
    }

    private function valueFor(string $branch, string $key): mixed
    {
        return SchoolBranchSetting::where('school_branch_id', $branch)
            ->whereHas('settingDefination', fn($query) => $query->where('key', $key))
            ->value('value');
    }

    public function test_every_branch_gets_every_setting_with_its_default(): void
    {
        $this->sync();

        $definitions = SettingDefination::count();
        $this->assertSame($definitions, SchoolBranchSetting::where('school_branch_id', 'branch-1')->count());
        $this->assertSame($definitions, SchoolBranchSetting::where('school_branch_id', 'branch-2')->count());
        $this->assertEquals(30, $this->valueFor('branch-1', 'additionalFee.default_due_days'));
    }

    public function test_a_value_the_school_chose_is_kept_and_a_second_run_adds_nothing(): void
    {
        $this->sync();
        $rows = SchoolBranchSetting::count();

        $this->sync();

        $this->assertSame($rows, SchoolBranchSetting::count());
        $this->assertEquals(2.5, $this->valueFor('branch-1', 'grade.passing_gpa'));
    }

    public function test_the_deploy_seeder_runs_the_sync(): void
    {
        $calls = file_get_contents((new \ReflectionClass(DatabaseSeeder::class))->getFileName());

        $this->assertMatchesRegularExpression('/SettingDefinationSeeder::class\);\s*(\/\/[^\n]*\n\s*)?\$this->call\(SyncSchoolBranchSettingSeeder::class\)/', $calls, 'The sync runs right after the definitions are seeded.');
    }
}
