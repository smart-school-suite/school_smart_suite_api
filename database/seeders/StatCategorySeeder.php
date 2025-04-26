<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker as Faker;
class StatCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $categoryList = [
            [
                "name" => 'Student Exam Statistics',
                "program_name" => "student_exam_stats",
            ],
            [
                "name" => 'Exam Statistics',
                "program_name" => "exam_stats"
            ]
        ];
        $faker = Faker\Factory::create();
        foreach ($categoryList as $category) {
            DB::table('stat_categories')->insert([
                'id' => $faker->uuid,
                'name' => $category['name'],
                'program_name' => $category['program_name'],
                'description' => $faker->sentence,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
