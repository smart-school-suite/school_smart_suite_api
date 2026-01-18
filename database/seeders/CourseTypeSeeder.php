<?php

namespace Database\Seeders;

use App\Models\Course\CourseType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseTypes = [
            [
                "name" => "Practical",
                "key" => "practical.course",
                "description" => "Focuses on concepts and ideas, using lectures, reading, and discussions.",
                "text_color" => "#3d889d",
                "background_color" => "#bcdee5"
            ],
            [
                "name" => "Theoritical",
                "key" => "theoritical.course",
                "description" => "Emphasizes hands-on skills and real-world application through exercises, projects, and activities.",
                "text_color" => "#ffccc6",
                "background_color" => "#fd614f"
            ]
        ];

        foreach ($courseTypes as $courseType ) {
            CourseType::create($courseType);
        }
    }
}
