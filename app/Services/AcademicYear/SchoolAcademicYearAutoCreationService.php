<?php

namespace App\Services\AcademicYear;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\FeeSchedule;
use App\Models\Promotion\PromotionRun;
use App\Models\Specialty;
use App\Services\AutoCreation\AutoCreationSettings;
use App\Services\AutoCreation\AutoCreationTargets;
use Illuminate\Support\Collection;

/**
 * Creates the automatically created copies of a newly created school
 * academic year, as set by the Academic Year Settings. Must be called
 * inside the transaction that created the original, so the original and
 * its copies are committed or rolled back together.
 *
 * Which specialty levels get a copy is decided by AutoCreationTargets. Each
 * copy has the original's system academic year and dates, and gets its own
 * fee schedule and promotion run, like an academic year created by hand. A
 * specialty that already has this academic year is skipped.
 */
class SchoolAcademicYearAutoCreationService
{
    public function __construct(private AutoCreationTargets $targets) {}

    /** @return Collection<int, SchoolAcademicYear> the academic years created */
    public function createFor(SchoolAcademicYear $original, Specialty $specialty): Collection
    {
        $branchId = $original->school_branch_id;
        $settings = AutoCreationSettings::forBranch($branchId, AutoCreationSettings::ACADEMIC_YEAR);

        $created = collect();

        foreach ($this->targets->for($specialty, $settings) as $target) {
            $exists = SchoolAcademicYear::where('school_branch_id', $branchId)
                ->where('specialty_id', $target->id)
                ->where('system_academic_year_id', $original->system_academic_year_id)
                ->exists();

            if ($exists) {
                continue;
            }

            $academicYear = SchoolAcademicYear::create([
                'specialty_id' => $target->id,
                'school_branch_id' => $branchId,
                'system_academic_year_id' => $original->system_academic_year_id,
                'start_date' => $original->start_date,
                'end_date' => $original->end_date,
            ]);

            FeeSchedule::create([
                'school_branch_id' => $branchId,
                'school_year_id' => $academicYear->id,
            ]);

            PromotionRun::create([
                'school_year_id' => $academicYear->id,
                'school_branch_id' => $branchId,
                'total_students' => $target->student()->count(),
            ]);

            $created->push($academicYear);
        }

        return $created;
    }
}
