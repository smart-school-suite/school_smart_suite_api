<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Badge;

class StudentBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batches = [
            [
                'name' => 'Golden',
                'color' => "#FFC107",
                'desktop_icon' => json_encode("ph:seal-check-fill")
            ],
            [
                'name' => 'Blue',
                'color' => "#1E90FF ",
                'desktop_icon' => json_encode("gravity-ui:circle-check-fill")
            ]
        ];

        foreach($batches as $batch){
            Badge::create($batch);
        }
    }
}
