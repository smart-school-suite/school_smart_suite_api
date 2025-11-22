<?php

namespace Database\Seeders;

use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use Illuminate\Database\Seeder;
use App\Models\SettingDefination;
use App\Models\SchoolBranchSetting;
use App\Models\SettingCategory;
use Illuminate\Support\Facades\Hash;

class test extends Seeder
{
    public function run(): void {
       $schoolAdmin = Schooladmin::where("email", "chongongprecious@gmail.com")->first();
       $schoolAdmin->password = Hash::make("Keron484$");
       $schoolAdmin->save();
    }

    public function electionSettingSeeder(){
                 $data = [
            [
                'name' => 'Additional Fee Settings',
                'key' => 'setting.category.additionalFee'
            ],
            [
                'name' => 'Exam Settings',
                'key' => 'setting.category.exam'
            ],
            [
                'name' => 'Resit Settings',
                'key' => 'setting.category.resit'
            ],
            [
                'name' => 'Time-table Settings',
                'key' => 'setting.category.timetable'
            ],
            [
                'name' => 'Student Promotion Setting',
                'key' => 'setting.category.promotion'
            ],
            [
                'name' => 'Grade Settings',
                'key' => 'setting.category.grade'
            ],
            [
                'name' => 'Election Tie Breaker Setting',
                'key' => 'setting.category.election.tie.breaker'
            ]

        ];

        foreach ($data as $setting) {
            $settingCategory = SettingCategory::where("name", $setting['name'])->first();
            $settingCategory->key = $setting['key'];
            $settingCategory->save();
        }
    }
}
