<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Educationlevels;
use App\Models\LevelType;
use Illuminate\Support\Facades\Log;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programLevels100 = [
            ["name" => "Master's Degree One", "level" => 500, "program_name" => "phd"],
            ["name" => "Bachelor's Degree Programs", "level" => 400, "program_name" => "bachelor_degree"],
            ["name" => "Level One", "level" => 100, "program_name" => "level_one"],
            ["name" => "Doctoral (Ph.D.) Programs", "level" => 700, "program_name" => "master_one_degree"],
            ["name" => "Master's Degree Two", "level" => 600, "program_name" => "master_two_degree"],
            ["name" => "Level Three", "level" => 300, "program_name" => "level_three"],
            ["name" => "Level Two", "level" => 200, "program_name" => "level_two"],
        ];

        $programLevels200 = [
            ["name" => "Level One", "level" => 200, "program_name" => "level_one"],
            ["name" => "Level Two", "level" => 300, "program_name" => "level_two"],
            ["name" => "Level Three", "level" => 400, "program_name" => "level_three"],
            ["name" => "Bachelor's Degree Programs", "level" => 500, "program_name" => "bachelor_degree"],
            ["name" => "Master's Degree One", "level" => 600, "program_name" => "masters_one"],
            ["name" => "Master's Degree Two", "level" => 700, "program_name" => "masters_two"],
            ["name" => "Doctoral (Ph.D.) Programs", "level" => 800, "program_name" => "phd_program"],
        ];

        $levelTypes = LevelType::whereIn('program_name', ['level_start_100', 'level_start_200'])
            ->get()
            ->keyBy('program_name');

        $this->seedLevels($programLevels100, $levelTypes->get('level_start_100'));
        $this->seedLevels($programLevels200, $levelTypes->get('level_start_200'));
    }

    private function seedLevels(array $programLevels, ?LevelType $levelType): void
    {
        if (!$levelType) {
            Log::warning('LevelType not found — skipping seed group.');
            return;
        }

        foreach ($programLevels as $programLevel) {
            Educationlevels::updateOrCreate(
                [
                    'level_type_id' => $levelType->id,
                    'program_name' => $programLevel['program_name'],
                ],
                [
                    'name' => $programLevel['name'],
                    'level' => $programLevel['level'],
                ]
            );
        }
    }
}
