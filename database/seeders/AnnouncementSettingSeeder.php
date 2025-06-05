<?php

namespace Database\Seeders;

use App\Models\AnnouncementSetting;
use App\Models\SchoolAnnouncementSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnouncementSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = AnnouncementSetting::all();
        foreach($settings as $setting){
           SchoolAnnouncementSetting::create([
                'announcement_setting_id' => $setting['id'],
                'school_branch_id' => "a6e8ecad-e331-4500-b076-655c89d68e02"
           ]);
        }
    }
}
