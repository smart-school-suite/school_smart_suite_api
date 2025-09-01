<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GradesCategory;
class GradeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createGradeCategory();
    }

     private function createGradeCategory()
    {
        $categories = [
            ['title' => 'Level One CA', 'status' => 'active', 'exam_type' => 'ca'],
            ['title' => 'Level One Exam', 'status' => 'active', 'exam_type' => 'exam'],
            ['title' => 'Level One Resit', 'status' => 'active', 'exam_type' => 'resit'],
            ['title' => 'Level Two CA', 'status' => 'active', 'exam_type' =>  'ca'],
            ['title' => 'Level Two Exam', 'status' => 'active', 'exam_type' => 'exam'],
            ['title' => 'Level Two Resit', 'status' => 'active', 'exam_type' => 'resit'],
            ['title' => 'Level Three CA', 'status' => 'active', 'exam_type' =>  'ca'],
            ['title' => 'Level Three Exam', 'status' => 'active', 'exam_type' => 'exam'],
            ['title' => 'Level Three Resit', 'status' => 'active', 'exam_type' => 'resit'],
            ['title' => 'Bachelors Degree CA', 'status' => 'active', 'exam_type' =>  'ca'],
            ['title' => 'Bachelors Degree Exam', 'status' => 'active', 'exam_type' => 'exam'],
            ['title' => 'Bachelors Degree Resit', 'status' => 'active', 'exam_type' =>  'ca'],
            ['title' => 'Masters Degree CA', 'status' => 'active', 'exam_type' =>  'ca'],
            ['title' => 'Masters Degree Exam', 'status' => 'active', 'exam_type' => 'exam'],
            ['title' => 'Masters Degree Resit', 'status' => 'active', 'exam_type' => 'resit'],
        ];
        foreach ($categories as $category) {
            GradesCategory::create($category);
        }
    }
}
