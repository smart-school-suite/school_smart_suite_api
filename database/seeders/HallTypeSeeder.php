<?php

namespace Database\Seeders;

use App\Models\HallType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HallTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hallTypes = [
            [
                 "name" => "Exam Hall",
                 "key" => "exam.hall",
                 "description" => "Hall Used For Writing Exams",
                 "text_color" => "#a987cb",
                 "background_color" => "#e7e1f3"
            ],
            [
                 "name" => "Lecture Hall",
                 "key" => "lecture.hall",
                 "description" => "Hall Used For Lectures",
                 "text_color" => "#22aca5",
                 "background_color" => "#d0f7f1"
            ],
            [
                 "name" => "Practical",
                 "key" => "practical.hall",
                 "description" => "Hall Used For Practicals",
                 "text_color" => "#e74db8",
                 "background_color" => "#fbe8f7"
             ]
        ];

        foreach($hallTypes as $hallType){
              HallType::create($hallType);
        }
    }
}
