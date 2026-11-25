<?php

namespace Tests\Feature\AutoCreation;

use App\Models\SchoolBranchSetting;
use App\Models\SettingDefination;
use Database\Seeders\LevelSeeder;
use Database\Seeders\LevelTypeSeeder;
use Database\Seeders\SettingCategorySeeder;
use Database\Seeders\SettingDefinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AutoCreationSettingsUpgradeTest extends TestCase
{
    use RefreshDatabase;

    private function oldSetting(string $key, string $type, array $valuesByBranch, ?string $name = null): void
    {
        $category = DB::table('setting_categories')->where('key', 'setting.category.academic_year')->value('id');
        DB::table('setting_definations')->insert(['id' => "old-$key", 'name' => $name ?? $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => $category]);
        foreach ($valuesByBranch as $branch => $value) {
            SchoolBranchSetting::create(['school_branch_id' => $branch, 'setting_defination_id' => "old-$key", 'value' => $value]);
        }
    }

    private function value(string $branch, string $key): mixed
    {
        return SchoolBranchSetting::where('school_branch_id', $branch)
            ->whereHas('settingDefination', fn ($query) => $query->where('key', $key))
            ->first()?->value;
    }

    public function test_old_modes_become_the_new_switch_and_one_option(): void
    {
        $this->seed([LevelTypeSeeder::class, LevelSeeder::class, SettingCategorySeeder::class]);
        foreach (['off', 'same', 'across'] as $branch) {
            DB::table('school_branches')->insert(['id' => $branch, 'name' => $branch, 'abbreviation' => $branch, 'school_id' => 'school-1']);
        }
        $this->oldSetting('academic_year.auto_create_mode', 'string', ['off' => 'disabled', 'same' => 'same_specialty', 'across' => 'across_specialties'], 'Automatic Academic Year Creation');
        $this->oldSetting('academic_year.auto_create_across_all_levels', 'boolean', ['across' => true]);

        $this->seed(SettingDefinationSeeder::class);

        $prefix = 'academic_year.auto_create_';
        $expected = [
            'off' => [false, 'across_department_specialty'],
            'same' => [true, 'across_department_specialty'],
            'across' => [true, 'across_department_levels'],
        ];
        foreach ($expected as $branch => [$enabled, $scope]) {
            $this->assertSame($enabled, $this->value($branch, $prefix . 'enabled'), $branch);
            foreach (['across_levels', 'across_specialty', 'across_department_levels', 'across_department_specialty'] as $name) {
                $this->assertSame($name === $scope, $this->value($branch, $prefix . $name), "$branch $name");
            }
        }

        $this->assertNull(SettingDefination::where('key', $prefix . 'mode')->first());
        $this->assertNull(SettingDefination::where('key', $prefix . 'across_all_levels')->first());
        $this->assertSame(0, SchoolBranchSetting::where('setting_defination_id', 'old-academic_year.auto_create_mode')->count());

        $this->seed(SettingDefinationSeeder::class);
        $this->assertTrue($this->value('across', $prefix . 'across_department_levels'));
    }
}
