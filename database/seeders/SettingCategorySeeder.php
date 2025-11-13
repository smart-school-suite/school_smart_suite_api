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
            SettingCategory::create([
                'name' => $setting['name'],
                'key' => $setting['key'],
            ]);
        }
    }
}
