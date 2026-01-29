<?php

namespace App\Jobs\SystemAcademicYear;

use App\Models\AcademicYear\SystemAcademicYear;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateSystemAcademicYearJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function handle(): void
    {
        $currentYear = now()->year;

        $ranges = [
            [$currentYear, $currentYear + 1],
            [$currentYear + 1, $currentYear + 2],
        ];

        foreach ($ranges as [$start, $end]) {
            $exists = SystemAcademicYear::where('year_start', $start)
                ->where('year_end', $end)
                ->exists();

            if ($exists) {
                continue;
            }

            SystemAcademicYear::create([
                'name'       => "{$start}-{$end}",
                'year_start' => $start,
                'year_end'   => $end,
            ]);
        }
    }
}
