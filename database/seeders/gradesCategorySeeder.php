<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\GradesCategory;
use Illuminate\Database\Seeder;

class gradesCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          // Define the categories
          $categories = [
            ['title' => 'Level One CA', 'status' => 'active'],
            ['title' => 'Level One Exam', 'status' => 'active'],
            ['title' => 'Level One Resit', 'status' => 'active'],
            ['title' => 'Level Two CA', 'status' => 'active'],
            ['title' => 'Level Two Exam', 'status' => 'active'],
            ['title' => 'Level Two Resit', 'status' => 'active'],
            ['title' => 'Level Three CA', 'status' => 'active'],
            ['title' => 'Level Three Exam', 'status' => 'active'],
            ['title' => 'Level Three Resit', 'status' => 'active'],
            ['title' => 'Bachelors Degree CA', 'status' => 'active'],
            ['title' => 'Bachelors Degree Exam', 'status' => 'active'],
            ['title' => 'Bachelors Degree Resit', 'status' => 'active'],
            ['title' => 'Masters Degree CA', 'status' => 'active'],
            ['title' => 'Masters Degree Exam', 'status' => 'active'],
            ['title' => 'Masters Degree Resit', 'status' => 'active'],
        ];

        // Insert the categories into the database
        foreach ($categories as $category) {
            GradesCategory::create($category);
        }

    }
}
