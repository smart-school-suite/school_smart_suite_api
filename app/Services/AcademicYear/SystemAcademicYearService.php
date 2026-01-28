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
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;

        $academicYearSwitchMonth = 8;

        $academicStartYear = $currentDate->month >= $academicYearSwitchMonth
            ? $currentYear
            : $currentYear - 1;

        $academicYears = SystemAcademicYear::query()
            ->whereIn('year_start', [
                $academicStartYear,
                $academicStartYear + 1,
            ])
            ->orderBy('year_start', 'asc')
            ->get();

        $result = collect();

        if ($academicYears->isEmpty()) {
            return $result;
        }

        foreach ($academicYears as $year) {
            $label = $year->year_start === $academicStartYear
                ? 'current'
                : 'future';

            $result->push((object) [
                'academic_year' => $year,
                'status' => $label,
                'display_name' => $label === 'current' ? 'Current Academic Year' : 'Next Academic Year',
            ]);
        }

        return $result;
    }
}
