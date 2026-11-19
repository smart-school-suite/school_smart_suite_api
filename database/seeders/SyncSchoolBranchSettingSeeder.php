<?php

namespace Database\Seeders;

use App\Models\Schoolbranches;
use App\Models\SettingDefination;
use App\Models\SchoolBranchSetting;
use Illuminate\Database\Seeder;

/**
 * Gives every school branch a row for every setting definition. A branch
 * gets its rows when it subscribes; settings defined later (by
 * SettingDefinationSeeder) only reach existing branches through this
 * seeder, and the settings screen lists a branch's rows only.
 *
 * Missing rows are created with the setting's default. Rows that exist are
 * never changed, so a value a school has chosen is kept. Safe to run on
 * every deploy (DatabaseSeeder calls it).
 */
class SyncSchoolBranchSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settingDefs = SettingDefination::all();
        if ($settingDefs->isEmpty()) {
            return;
        }

        $created = 0;
        Schoolbranches::query()->select('id')->each(function ($schoolBranch) use ($settingDefs, &$created) {
            $existing = SchoolBranchSetting::where('school_branch_id', $schoolBranch->id)
                ->pluck('setting_defination_id')
                ->flip();

            foreach ($settingDefs as $settingDef) {
                if ($existing->has($settingDef->id)) {
                    continue;
                }
                SchoolBranchSetting::create([
                    'school_branch_id' => $schoolBranch->id,
                    'setting_defination_id' => $settingDef->id,
                    'value' => $settingDef->default_value ?? null,
                ]);
                $created++;
            }
        });

        if ($created > 0) {
            $this->command?->info("Added {$created} missing school branch settings.");
        }
    }
}
