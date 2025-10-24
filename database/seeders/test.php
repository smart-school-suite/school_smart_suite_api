<?php

namespace Database\Seeders;

use App\Models\Schoolbranches;
use Illuminate\Database\Seeder;
use App\Models\SettingDefination;
use App\Models\SchoolBranchSetting;
class test extends Seeder
{
    public function run(): void {
         $settingDefs = SettingDefination::all();
         $schoolBranch = Schoolbranches::first();
         foreach($settingDefs as $settingDef){
            SchoolBranchSetting::create([
                 'school_branch_id' => $schoolBranch->id,
                 'setting_defination_id' => $settingDef->id,
                 'value' => $settingDef->default_value
            ]);
         }
    }
}
