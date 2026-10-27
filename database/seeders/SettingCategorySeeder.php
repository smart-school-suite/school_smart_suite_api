<?php

namespace Database\Seeders;

use App\Models\SettingCategory;
use Illuminate\Database\Seeder;

class SettingCategorySeeder extends Seeder
{
    public function run(): void
    {
        $this->createSettingCategories();
    }

    public function createSettingCategories(): void
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
                'name' => 'Timetable Settings',
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
                'name' => 'Election Setting',
                'key' => 'setting.category.election.tie.breaker'
            ],
            [
                'name' => 'Level Settings',
                'key' => 'setting.category.level'
            ],
            [
                'name' => 'Graduation Settings',
                'key' => 'setting.graduation'
            ]
        ];

        foreach ($data as $setting) {
            try {
                $nameExists = SettingCategory::where('name', $setting['name'])
                    ->where('key', '!=', $setting['key'])
                    ->exists();

                if ($nameExists) {
                    continue;
                }

                SettingCategory::updateOrCreate(
                    ['key' => $setting['key']],
                    ['name' => $setting['name']]
                );
            } catch (\Throwable $e) {
            }
        }
    }
}
