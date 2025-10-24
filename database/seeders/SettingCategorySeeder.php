<?php

namespace Database\Seeders;

use App\Models\SettingCategory;
use Illuminate\Database\Seeder;

class SettingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $this->createSettingCategories();
    }

    public function createSettingCategories()
    {
        $data = [
            'Additional Fee Settings',
            'Exam Settings',
            'Resit Settings',
            'Time-table Settings',
            'Student Promotion Setting',
            'Grade Settings',
            'Election Tie Breaker Setting',

        ];

        foreach ($data as $setting) {
            SettingCategory::create([
                'name' => $setting
            ]);
        }
    }
}
