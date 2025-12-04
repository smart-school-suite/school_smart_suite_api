<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LevelType;

class LevelTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levelTypes = [
            [
                "name" => "100-Level Start",
                "description" => "Level numbering begins at 100 (100, 200, 300, ...). Most common numbering style.",
                "program_name" => "level_start_100"
            ],
            [
                "name" => "200-Level Start",
                "description" => "Level numbering begins at 200 (200, 300, 400, ...). Used by universities whose first academic year starts at Level 200.",
                "program_name" => "level_start_200"
            ],
            [
                "name" => "300-Level Start",
                "description" => "Level numbering begins at 300 (300, 400, 500, ...). Sometimes used for special direct-entry programs.",
                "program_name" => "level_start_300"
            ],
            [
                "name" => "400-Level Start",
                "description" => "Level numbering begins at 400. Usually applied to advanced or professional entry programs.",
                "program_name" => "level_start_400"
            ],
        ];
        foreach ($levelTypes as $levelType) {
            LevelType::create([
                'name' => $levelType['name'],
                'description' => $levelType['description'],
                'program_name' => $levelType['program_name']
            ]);
        }
    }
}
