<?php

namespace Database\Seeders;

use App\Models\SemesterTimetable\PeriodDuration;
use Illuminate\Database\Seeder;

class PeriodDurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $durations = [
            [
                'name' => 'Short Period',
                'minutes' => 30,
                'description' => 'A 30-minute slot typically used for consultations or quick practical check-ins.'
            ],
            [
                'name' => 'Standard Half Period',
                'minutes' => 45,
                'description' => 'A 45-minute duration suitable for secondary level lessons or short workshops.'
            ],
            [
                'name' => '1 Hour Period',
                'minutes' => 60,
                'description' => 'The standard 60-minute lecture unit for most academic semester courses.'
            ],
            [
                'name' => 'Double Period',
                'minutes' => 120,
                'description' => 'A 120-minute (2 hour) extended block reserved for intensive labs, studios, or exams.'
            ],
        ];

        PeriodDuration::upsert(
            $durations,
            ['minutes'], // Unique column(s) to check for duplicates
            ['name', 'description'] // Columns to update if duplicate found (empty array means no updates)
        );
    }
}
