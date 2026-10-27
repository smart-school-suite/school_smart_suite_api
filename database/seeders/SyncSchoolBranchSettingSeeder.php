<?php

namespace Database\Seeders;

use App\Models\Schoolbranches;
use App\Models\SettingDefination;
use App\Models\SchoolBranchSetting;
use Illuminate\Database\Seeder;

class SyncSchoolBranchSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settingDefs = SettingDefination::all();
        $schoolBranches = Schoolbranches::all();

        foreach ($schoolBranches as $schoolBranch) {
            foreach ($settingDefs as $settingDef) {
                SchoolBranchSetting::firstOrCreate(
                    [
                        'school_branch_id' => $schoolBranch->id,
                        'setting_defination_id' => $settingDef->id,
                    ],
                    [
                        'value' => $settingDef->default_value ?? null,
                    ]
                );
            }
        }
    }
}
