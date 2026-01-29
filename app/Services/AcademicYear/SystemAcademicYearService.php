<?php

namespace App\Services\AcademicYear;

use App\Models\AcademicYear\SystemAcademicYear;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SystemAcademicYearService
{
    public function getAllSystemAcademicYears(): Collection
    {
        return SystemAcademicYear::query()
            ->orderBy('year_start', 'desc')
            ->get();
    }
    public function getSystemAcademicYearByCurrentYear(): Collection
    {
        $now = Carbon::now();
        $currentYear = $now->year;
        $switchMonth = 8;

        $academicStartYear = $now->month >= $switchMonth
            ? $currentYear
            : $currentYear - 1;

        return SystemAcademicYear::query()
            ->whereIn('year_start', [
                $academicStartYear,
                $academicStartYear + 1,
            ])
            ->orderBy('year_start')
            ->get()
            ->map(function (SystemAcademicYear $year) use ($academicStartYear) {
                return [
                    'id'         => $year->id,
                    'name'       => $year->name,
                    'year_start' => $year->year_start,
                    'year_end'   => $year->year_end,
                    'label'      => $year->year_start === $academicStartYear
                        ? 'current'
                        : 'future',
                    'created_at' => $year->created_at,
                    'updated_at' => $year->updated_at,
                ];
            });
    }
}
