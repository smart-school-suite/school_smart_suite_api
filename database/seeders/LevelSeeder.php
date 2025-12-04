<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Educationlevels;
use App\Models\LevelType;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programLevels100 = [
            [
                "name" => "Master's Degree One",
                "level" => 500,
                "program_name" => "phd"
            ],
            [
                "name" => "Bachelor's Degree Programs",
                "level" => 400,
                "program_name" => "bachelor_degree"
            ],
            [
                "name" => "Level One",
                "level" => 100,
                "program_name" => "level_one"
            ],
            [
                "name" => "Doctoral (Ph.D.) Programs",
                "level" => 700,
                "program_name" => "master_one_degree"
            ],
            [
                "name" => "Master's Degree Two",
                "level" => 600,
                "program_name" => "master_two_degree"
            ],
            [
                "name" => "Level Three",
                "level" => 300,
                "program_name" => "level_three"
            ],
            [
                "name" => "Level Two",
                "level" => 200,
                "program_name" => "level_two"
            ],
        ];

        $programLevels200 = [
            [
                "name" => "Level One",
                "level" => 200,
                "program_name" => "level_one"
            ],
            [
                "name" => "Level Two",
                "level" => 300,
                "program_name" => "level_two"
            ],
            [
                "name" => "Level Three",
                "level" => 400,
                "program_name" => "level_three"
            ],
            [
                "name" => "Bachelor's Degree Programs",
                "level" => 500,
                "program_name" => "bachelor_degree"
            ],
            [
                "name" => "Master's Degree One",
                "level" => 600,
                "program_name" => "masters_one"
            ],
            [
                "name" => "Master's Degree Two",
                "level" => 700,
                "program_name" => "masters_two"
            ],
            [
                "name" => "Doctoral (Ph.D.) Programs",
                "level" => 800,
                "program_name" => "phd_program"
            ],
        ];

        foreach ($programLevels100 as $programLevel) {
            $levelType = LevelType::where("program_name", "level_start_100")->first();
            Educationlevels::create([
                'name' => $programLevel['name'],
                'level' => $programLevel['level'],
                'program_name' => $programLevel['program_name'],
                'level_type_id' => $levelType->id,
            ]);
        }

        foreach ($programLevels200 as $programLevel) {
            $levelType = LevelType::where("program_name", "level_start_200")->first();
            Educationlevels::create([
                'name' => $programLevel['name'],
                'level' => $programLevel['level'],
                'program_name' => $programLevel['program_name'],
                'level_type_id' => $levelType->id,
            ]);
        }
    }
}
