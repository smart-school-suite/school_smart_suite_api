<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear\SystemAcademicYear;

class SystemAcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = now()->year;

        $ranges = [
            [$currentYear, $currentYear + 1],
            [$currentYear + 1, $currentYear + 2],
        ];

        $validRanges = [];
        foreach ($ranges as [$start, $end]) {
            SystemAcademicYear::firstOrCreate(
                [
                    'year_start' => $start,
                    'year_end'   => $end,
                ],
                [
                    'name' => "{$start}-{$end}",
                ]
            );

            $validRanges[] = [$start, $end];
        }

        SystemAcademicYear::whereNotIn('year_start', array_column($validRanges, 0))
            ->delete();
    }
}
