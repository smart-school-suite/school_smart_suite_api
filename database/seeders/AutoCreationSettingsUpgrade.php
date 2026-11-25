<?php

namespace Database\Seeders;

use App\Models\SchoolBranchSetting;
use App\Models\SettingDefination;
use App\Services\AutoCreation\AutoCreationSettings as Settings;
use Illuminate\Support\Facades\DB;

/**
 * Carries each school's automatic creation choices over from the old
 * settings (mode, across_specialties, across_all_levels) to the new ones
 * (enabled and four scopes), before the old settings are removed. Runs
 * only while the old "mode" setting still exists, so it is safe to repeat.
 *
 *  - mode disabled            -> off, Across the Same Specialty in the Department
 *  - mode same_specialty      -> on,  Across the Same Specialty in the Department
 *  - mode across_specialties  -> on,  Across All Levels in the Department
 */
class AutoCreationSettingsUpgrade
{
    public function run(): void
    {
        foreach (Settings::PREFIXES as $prefix) {
            $this->upgrade($prefix);
        }
    }

    private function upgrade(string $prefix): void
    {
        $mode = SettingDefination::where('key', $prefix . 'mode')->first();
        if (!$mode) {
            return;
        }

        $definitions = SettingDefination::whereIn('key', array_map(
            fn($name) => $prefix . $name,
            [Settings::ENABLED, ...Settings::SCOPES]
        ))->pluck('id', 'key');

        if ($definitions->count() !== count(Settings::SCOPES) + 1) {
            return;
        }

        DB::transaction(function () use ($mode, $definitions, $prefix) {
            SchoolBranchSetting::where('setting_defination_id', $mode->id)->get()
                ->each(function (SchoolBranchSetting $row) use ($definitions, $prefix) {
                    $value = $row->value;
                    $scope = $value === 'across_specialties'
                        ? Settings::ACROSS_DEPARTMENT_LEVELS
                        : Settings::ACROSS_DEPARTMENT_SPECIALTY;

                    $values = [Settings::ENABLED => in_array($value, ['same_specialty', 'across_specialties'], true)];
                    foreach (Settings::SCOPES as $name) {
                        $values[$name] = $name === $scope;
                    }

                    foreach ($values as $name => $on) {
                        SchoolBranchSetting::updateOrCreate(
                            ['school_branch_id' => $row->school_branch_id, 'setting_defination_id' => $definitions[$prefix . $name]],
                            ['value' => $on]
                        );
                    }
                });
        });
    }
}
