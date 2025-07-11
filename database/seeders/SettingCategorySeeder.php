<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\SettingCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Additional Fee Settings',
            'Education Level Settings',
            'Department Settings',
            'Courses Settings',
            'Exam Settings',
            'Exam Time-table Settings',
            'Fee Schedule Settings',
            'Resit Settings',
            'Specialty Settings',
            'School Semester Settings',
            'Student Batch Settings',
            'Teacher Settings',
            'Subscription Settings',
            'Time-table Settings',
            'Student Settings'
        ];

        foreach($data as $setting){
           SettingCategory::create([
              'title' => $setting
           ]);
        }
    }
}
